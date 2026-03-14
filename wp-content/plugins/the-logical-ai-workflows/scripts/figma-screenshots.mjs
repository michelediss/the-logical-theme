#!/usr/bin/env node

import crypto from "node:crypto";
import fs from "node:fs/promises";
import path from "node:path";
import { chromium } from "playwright";
import {
  DEFAULT_FIGMA_CONFIG,
  appendHistory,
  ensurePageStructure,
  getArg,
  getPagePaths,
  hasFlag,
  loadFigmaConfig,
  loadManifest,
  nowIso,
  parseCliArgs,
  saveManifest,
  selectPages,
  upsertIndexPage,
  writeJson,
} from "./lib/ai-source-utils.mjs";

const VIEWPORTS = {
  desktop: { width: 1440, height: 1200 },
  mobile: { width: 390, height: 844 },
};

const FIGMA_READY_TIMEOUT_MS = 150000;
const FIGMA_GOTO_TIMEOUT_MS = 120000;
const FIGMA_PROBE_INTERVAL_MS = 5000;
const FIGMA_SCROLL_SETTLE_MS = 1500;
const FIGMA_MAX_TILES = 24;
const FIGMA_SCROLL_OVERLAP = 140;
const FIGMA_CAPTURE_ATTEMPTS = 2;
const FIGMA_REPEAT_RECOVERY_ATTEMPTS = 2;
const FIGMA_BLANK_HOST_STREAK = 3;
const FIGMA_JOIN_TOLERANCE_PX = 72;

function hashBuffer(buffer) {
  return crypto.createHash("sha1").update(buffer).digest("hex");
}

async function saveFigmaDebugArtifacts(page, outputPath, label) {
  const parsed = path.parse(outputPath);
  const safeLabel = String(label).replace(/[^a-z0-9_-]+/gi, "-").toLowerCase();
  const debugDir = path.join(parsed.dir, "debug");

  await fs.mkdir(debugDir, { recursive: true });

  const screenshotPath = path.join(debugDir, `${parsed.name}.${safeLabel}.host.png`);
  const htmlPath = path.join(debugDir, `${parsed.name}.${safeLabel}.host.html`);
  const statePath = path.join(debugDir, `${parsed.name}.${safeLabel}.state.json`);

  const html = await page.content().catch(() => "");
  const state = await page
    .evaluate(() => ({
      url: location.href,
      title: document.title,
      readyState: document.readyState,
      innerWidth: window.innerWidth,
      innerHeight: window.innerHeight,
      scrollY: window.scrollY,
      docScrollHeight: document.documentElement.scrollHeight,
      bodyScrollHeight: document.body?.scrollHeight ?? null,
      iframes: Array.from(document.querySelectorAll("iframe")).map((element, index) => {
        const rect = element.getBoundingClientRect();
        return {
          index,
          src: element.getAttribute("src"),
          x: rect.x,
          y: rect.y,
          width: rect.width,
          height: rect.height,
        };
      }),
      fixedOrSticky: Array.from(document.querySelectorAll("body *"))
        .map((element) => {
          if (!(element instanceof HTMLElement)) {
            return null;
          }

          const style = window.getComputedStyle(element);
          if (!["fixed", "sticky"].includes(style.position)) {
            return null;
          }

          const rect = element.getBoundingClientRect();
          if (rect.width <= 0 || rect.height <= 0) {
            return null;
          }

          return {
            tag: element.tagName,
            id: element.id || "",
            className: String(element.className || "").slice(0, 160),
            position: style.position,
            top: rect.top,
            left: rect.left,
            width: rect.width,
            height: rect.height,
            text: (element.innerText || element.textContent || "").trim().slice(0, 240),
          };
        })
        .filter(Boolean)
        .slice(0, 50),
      bodyTextSample: (document.body?.innerText || "").slice(0, 2000),
    }))
    .catch((error) => ({
      stateCaptureError: String(error.message || error),
    }));

  await Promise.all([
    page.screenshot({ path: screenshotPath, fullPage: true }).catch(() => null),
    fs.writeFile(htmlPath, html, "utf8"),
    fs.writeFile(statePath, `${JSON.stringify(state, null, 2)}\n`, "utf8"),
  ]);

  console.error(`Saved Figma debug artifacts: ${screenshotPath}`);
}

async function tryAcceptFigmaCookies(page) {
  const buttonTexts = [
    "Allow all cookies",
    "Do not allow cookies",
  ];

  let clicked = false;
  for (const text of buttonTexts) {
    const locator = page.getByRole("button", { name: text }).first();
    try {
      if (await locator.isVisible({ timeout: 1000 })) {
        await locator.click({ timeout: 2000 }).catch(() => null);
        clicked = true;
        break;
      }
    } catch {
      // ignore transient UI probes
    }
  }

  return clicked;
}

async function hideFigmaHostBanners(page) {
  const result = await page.evaluate(() => {
    const texts = [
      "This website uses cookies",
      "Allow all cookies",
      "Do not allow cookies",
      "Cookies settings",
      "Improve performance by enabling hardware acceleration",
      "[INTERNAL ONLY] Interactive elements cannot have other interactive elements nested inside of them.",
    ];
    const selectorHints = [
      "[class*='cookie']",
      "[class*='loading_indicator']",
      "[class*='blocked_ui_loading_indicator']",
      "[class*='visual_bell']",
    ];

    const hidden = new Set();
    const matchesText = (value) => texts.some((text) => value.includes(text));
    const isVisible = (element) => {
      if (!(element instanceof HTMLElement)) {
        return false;
      }

      const style = window.getComputedStyle(element);
      if (style.display === "none" || style.visibility === "hidden" || Number(style.opacity) === 0) {
        return false;
      }

      const rect = element.getBoundingClientRect();
      return rect.width > 0 && rect.height > 0;
    };

    const markHidden = (element, reason) => {
      if (!(element instanceof HTMLElement)) {
        return false;
      }
      const tagName = element.tagName.toLowerCase();
      if (["html", "body"].includes(tagName)) {
        return false;
      }
      if (element.id === "react-page") {
        return false;
      }
      if (hidden.has(element)) {
        return false;
      }
      element.setAttribute("data-ai-hidden-host-banner", reason);
      element.style.setProperty("display", "none", "important");
      hidden.add(element);
      return true;
    };

    let hiddenCount = 0;
    for (const node of Array.from(document.querySelectorAll("body *"))) {
      if (!(node instanceof HTMLElement) || !isVisible(node)) {
        continue;
      }

      const text = (node.innerText || node.textContent || "").trim();
      const selectorMatch = selectorHints.some((hint) => {
        try {
          return node.matches(hint);
        } catch {
          return false;
        }
      });

      if ((!text || !matchesText(text)) && !selectorMatch) {
        continue;
      }

      let target = node;
      const originalRect = target.getBoundingClientRect();
      while (target.parentElement) {
        const parent = target.parentElement;
        const parentTag = parent.tagName.toLowerCase();
        if (["html", "body"].includes(parentTag) || parent.id === "react-page") {
          break;
        }

        const style = window.getComputedStyle(parent);
        const rect = parent.getBoundingClientRect();
        const fixedLike = style.position === "fixed" || style.position === "sticky";
        const overlaysBottom = rect.bottom >= window.innerHeight - 4;
        const overlaysTop = rect.top <= 12 && rect.height <= 100;
        const parentTooLarge =
          rect.width >= window.innerWidth * 0.98 &&
          rect.height >= window.innerHeight * 0.98;
        const expandsTooMuch =
          rect.width > originalRect.width * 1.75 ||
          rect.height > originalRect.height * 1.75;

        if (parentTooLarge || expandsTooMuch) {
          break;
        }

        if (fixedLike || overlaysBottom || overlaysTop) {
          target = parent;
        } else {
          break;
        }
      }

      if (markHidden(target, "text-match")) {
        hiddenCount += 1;
      }
    }

    return { hiddenCount };
  });

  if (result.hiddenCount > 0) {
    console.log(`Hidden ${result.hiddenCount} host banner element(s) via fallback`);
  } else {
    console.log("No visible host banner required fallback hiding");
  }

  return result.hiddenCount;
}

async function waitForFigmaPreview(page) {
  const maxAttempts = Math.ceil(FIGMA_READY_TIMEOUT_MS / FIGMA_PROBE_INTERVAL_MS);
  let blankHostStreak = 0;

  for (let attempt = 1; attempt <= maxAttempts; attempt += 1) {
    if (attempt === 1 || attempt % 4 === 0) {
      await tryAcceptFigmaCookies(page);
    }

    await page.waitForTimeout(FIGMA_PROBE_INTERVAL_MS);

    const probe = await page.evaluate(() => {
      const frames = Array.from(document.querySelectorAll("iframe"));
      const previewFrames = frames
        .filter((element) => element.getAttribute("src")?.includes("figmaiframepreview.figma.site"))
        .map((element) => {
          const rect = element.getBoundingClientRect();
          return {
            x: rect.x,
            y: rect.y,
            width: rect.width,
            height: rect.height,
            src: element.getAttribute("src"),
          };
        });

      previewFrames.sort((left, right) => right.width * right.height - left.width * left.height);
      const bodyText = document.body?.innerText || "";
      const visibleNodeCount = Array.from(document.querySelectorAll("body *")).filter((element) => {
        if (!(element instanceof HTMLElement)) {
          return false;
        }

        const style = window.getComputedStyle(element);
        if (style.display === "none" || style.visibility === "hidden" || Number(style.opacity) === 0) {
          return false;
        }

        const rect = element.getBoundingClientRect();
        return rect.width > 0 && rect.height > 0;
      }).length;

      return {
        preview: previewFrames[0] || null,
        loadingVisible: /\bLoading\b/i.test(bodyText),
        bodyTextLength: bodyText.trim().length,
        visibleNodeCount,
        docScrollHeight: document.documentElement.scrollHeight,
        viewportHeight: window.innerHeight,
      };
    });
    const elapsedSeconds = attempt * (FIGMA_PROBE_INTERVAL_MS / 1000);
    const resolvedPreview =
      probe.preview &&
      probe.preview.width >= 200 &&
      probe.preview.height >= 200 &&
      !probe.loadingVisible
        ? probe.preview
        : null;

    if (resolvedPreview) {
      const viewportSize = page.viewportSize();
      const padding = 8;
      const x = Math.max(0, Math.min(resolvedPreview.x, (viewportSize?.width ?? resolvedPreview.width) - padding));
      const y = Math.max(0, Math.min(resolvedPreview.y, (viewportSize?.height ?? resolvedPreview.height) - padding));
      const width = Math.max(1, Math.min(resolvedPreview.width - padding * 2, (viewportSize?.width ?? resolvedPreview.width) - x));
      const height = Math.max(1, Math.min(resolvedPreview.height - padding * 2, (viewportSize?.height ?? resolvedPreview.height) - y));
      const sourceLabel = "iframe rect";
      console.log(`Found Figma preview after ${elapsedSeconds}s using ${sourceLabel}`);
      await page.waitForTimeout(1000);
      return { mode: "host-clip", clip: { x, y, width, height } };
    }

    const blankHost =
      probe.preview &&
      probe.preview.width === 0 &&
      probe.preview.height === 0 &&
      !probe.loadingVisible &&
      probe.bodyTextLength === 0 &&
      probe.visibleNodeCount <= 2 &&
      probe.docScrollHeight <= probe.viewportHeight + 8;

    blankHostStreak = blankHost ? blankHostStreak + 1 : 0;
    if (blankHostStreak >= FIGMA_BLANK_HOST_STREAK) {
      throw new Error("Figma host entered blank render state before preview became visible");
    }

    console.log(
      `Still waiting for Figma preview iframe: ${elapsedSeconds}s elapsed${probe.preview ? ` (rect ${Math.round(probe.preview.width)}x${Math.round(probe.preview.height)}, loading=${probe.loadingVisible}, blankHost=${blankHostStreak})` : ""}`,
    );
  }

  throw new Error(`Figma preview iframe was not ready after ${Math.round(FIGMA_READY_TIMEOUT_MS / 1000)}s`);
}

async function captureFigmaTile(page, clip) {
  return page.screenshot({
    clip: {
      x: clip.x,
      y: clip.y,
      width: clip.width,
      height: clip.height,
    },
    type: "png",
  });
}

async function advanceFigmaPreview(page, centerX, centerY, scrollStep, mode = "normal") {
  await page.mouse.move(centerX, centerY);
  await page.mouse.wheel(0, scrollStep);
  await page.waitForTimeout(FIGMA_SCROLL_SETTLE_MS);

  if (mode === "recovery") {
    await page.mouse.wheel(0, Math.max(120, Math.floor(scrollStep * 0.6)));
    await page.waitForTimeout(FIGMA_SCROLL_SETTLE_MS);
    await page.keyboard.press("PageDown").catch(() => null);
    await page.waitForTimeout(FIGMA_SCROLL_SETTLE_MS);
  }
}

function getFigmaPreviewFrame(page) {
  return page.frames().find((frame) => frame.url().includes("figmaiframepreview.figma.site")) || null;
}

async function readFigmaPreviewScrollState(page) {
  const frame = getFigmaPreviewFrame(page);
  if (!frame) {
    return null;
  }

  try {
    return await frame.evaluate(() => ({
      scrollY: window.scrollY,
      scrollHeight: Math.max(
        document.documentElement?.scrollHeight || 0,
        document.body?.scrollHeight || 0,
      ),
      innerHeight: window.innerHeight,
    }));
  } catch {
    return null;
  }
}

async function stitchFigmaTiles(browser, outputPath, tiles, overlapPx) {
  const width = tiles[0].width;
  const composePage = await browser.newPage({
    viewport: { width, height: 1400 },
  });

  try {
    await composePage.setContent(
      "<!doctype html><html><head><style>html,body{margin:0;padding:0;background:#fff;}canvas{display:block;}</style></head><body><canvas id='out'></canvas></body></html>",
      { waitUntil: "domcontentloaded" },
    );

    const stitchResult = await composePage.evaluate(
      async ({ width: canvasWidth, overlapPx: overlap, tiles: encodedTiles }) => {
        const canvas = document.getElementById("out");
        if (!(canvas instanceof HTMLCanvasElement)) {
          throw new Error("Missing output canvas");
        }

        const loadImage = (dataUrl) =>
          new Promise((resolve, reject) => {
            const image = new Image();
            image.onload = () => resolve(image);
            image.onerror = () => reject(new Error("Failed to load stitched tile image"));
            image.src = dataUrl;
          });

        const buildLuma = (image, width, height) => {
          const scratch = document.createElement("canvas");
          scratch.width = width;
          scratch.height = height;
          const scratchCtx = scratch.getContext("2d");
          if (!scratchCtx) {
            throw new Error("Missing scratch canvas context");
          }

          scratchCtx.drawImage(image, 0, 0, width, height);
          const { data } = scratchCtx.getImageData(0, 0, width, height);
          const luma = new Uint8Array(width * height);
          for (let pixel = 0; pixel < width * height; pixel += 1) {
            const offset = pixel * 4;
            luma[pixel] = Math.round(
              data[offset] * 0.299 +
              data[offset + 1] * 0.587 +
              data[offset + 2] * 0.114,
            );
          }
          return luma;
        };

        const compareOverlap = (previous, current, width, overlapHeight) => {
          let diff = 0;
          const sampleStepX = Math.max(1, Math.floor(width / 24));
          const sampleStepY = Math.max(1, Math.floor(overlapHeight / 48));
          for (let y = 0; y < overlapHeight; y += sampleStepY) {
            const previousRow = (previous.height - overlapHeight + y) * width;
            const currentRow = y * width;
            for (let x = 0; x < width; x += sampleStepX) {
              diff += Math.abs(previous.luma[previousRow + x] - current.luma[currentRow + x]);
            }
          }
          return diff;
        };

        const findBestOverlap = (previous, current, defaultOverlap, expectedOverlap, tolerancePx) => {
          const fallbackMinOverlap = Math.max(40, Math.floor(defaultOverlap * 0.45));
          const maxOverlap = Math.min(
            previous.height - 1,
            current.height - 1,
            Math.max(fallbackMinOverlap, Math.floor(defaultOverlap * 2.2)),
          );
          const minOverlap = expectedOverlap != null
            ? Math.max(24, Math.min(maxOverlap, expectedOverlap - tolerancePx))
            : fallbackMinOverlap;
          const boundedMaxOverlap = expectedOverlap != null
            ? Math.max(minOverlap, Math.min(maxOverlap, expectedOverlap + tolerancePx))
            : maxOverlap;

          let best = {
            overlap: Math.min(expectedOverlap ?? defaultOverlap, boundedMaxOverlap),
            score: Number.POSITIVE_INFINITY,
          };

          for (let overlapHeight = minOverlap; overlapHeight <= boundedMaxOverlap; overlapHeight += 4) {
            const score = compareOverlap(previous, current, previous.width, overlapHeight);
            if (score < best.score) {
              best = { overlap: overlapHeight, score };
            }
          }

          return best;
        };

        const preparedTiles = [];
        for (const tile of encodedTiles) {
          const image = await loadImage(tile.dataUrl);
          preparedTiles.push({
            image,
            width: tile.width,
            height: tile.height,
            luma: buildLuma(image, tile.width, tile.height),
          });
        }

        const overlaps = [];
        let totalHeight = preparedTiles[0]?.height || 0;
        for (let index = 1; index < preparedTiles.length; index += 1) {
          const hint = encodedTiles[index].expectedOverlap ?? null;
          const tolerance = encodedTiles[index].overlapTolerance ?? 48;
          const match = findBestOverlap(preparedTiles[index - 1], preparedTiles[index], overlap, hint, tolerance);
          overlaps.push(match);
          totalHeight += Math.max(1, preparedTiles[index].height - match.overlap);
        }

        canvas.width = canvasWidth;
        canvas.height = totalHeight;
        const ctx = canvas.getContext("2d");
        if (!ctx) {
          throw new Error("Missing 2D canvas context");
        }

        let offsetY = 0;
        for (let index = 0; index < preparedTiles.length; index += 1) {
          const tile = preparedTiles[index];
          if (index === 0) {
            ctx.drawImage(tile.image, 0, offsetY, tile.width, tile.height);
            offsetY += tile.height;
            continue;
          }

          const resolvedOverlap = overlaps[index - 1]?.overlap ?? overlap;
          const sourceY = Math.max(0, resolvedOverlap);
          const drawHeight = Math.max(1, tile.height - sourceY);
          ctx.drawImage(
            tile.image,
            0,
            sourceY,
            tile.width,
            drawHeight,
            0,
            offsetY - resolvedOverlap,
            tile.width,
            drawHeight,
          );
          offsetY += Math.max(1, tile.height - resolvedOverlap);
        }

        return {
          totalHeight,
          overlaps,
        };
      },
      {
        width,
        overlapPx,
        tiles: tiles.map((tile) => ({
          width: tile.width,
          height: tile.height,
          dataUrl: `data:image/png;base64,${tile.buffer.toString("base64")}`,
          expectedOverlap: tile.expectedOverlap ?? null,
          overlapTolerance: tile.overlapTolerance ?? FIGMA_JOIN_TOLERANCE_PX,
        })),
      },
    );

    await composePage.locator("#out").screenshot({ path: outputPath });
    return stitchResult;
  } finally {
    await composePage.close();
  }
}

async function captureFigmaFullPage(page, clip, browser, outputPath) {
  const effectiveOverlap = Math.min(FIGMA_SCROLL_OVERLAP, Math.max(40, Math.floor(clip.height * 0.2)));
  const scrollStep = Math.max(120, Math.floor(clip.height - effectiveOverlap));
  const centerX = Math.round(clip.x + clip.width / 2);
  const centerY = Math.round(clip.y + clip.height / 2);
  const tiles = [];
  const seenHashes = new Set();
  let repeatRecoveries = 0;

  console.log(
    `Starting Figma full-page capture with step ${scrollStep}px and overlap ${effectiveOverlap}px`,
  );

  for (let tileIndex = 0; tileIndex < FIGMA_MAX_TILES; tileIndex += 1) {
    const scrollState = await readFigmaPreviewScrollState(page);
    await hideFigmaHostBanners(page);
    const buffer = await captureFigmaTile(page, clip);
    const hash = hashBuffer(buffer);
    if (seenHashes.has(hash)) {
      if (repeatRecoveries < FIGMA_REPEAT_RECOVERY_ATTEMPTS) {
        repeatRecoveries += 1;
        console.log(
          `Reached repeated Figma tile at index ${tileIndex + 1}; trying recovery scroll ${repeatRecoveries}/${FIGMA_REPEAT_RECOVERY_ATTEMPTS}`,
        );
        await advanceFigmaPreview(page, centerX, centerY, scrollStep, "recovery");
        continue;
      }

      console.log(`Reached repeated Figma tile at index ${tileIndex + 1}; stopping stitch capture`);
      break;
    }

    repeatRecoveries = 0;
    seenHashes.add(hash);
    tiles.push({
      buffer,
      width: Math.round(clip.width),
      height: Math.round(clip.height),
      scrollY: typeof scrollState?.scrollY === "number" ? scrollState.scrollY : null,
    });
    console.log(`Captured Figma tile ${tiles.length}/${FIGMA_MAX_TILES}`);

    if (tileIndex === FIGMA_MAX_TILES - 1) {
      break;
    }

    await advanceFigmaPreview(page, centerX, centerY, scrollStep);
  }

  if (tiles.length === 0) {
    throw new Error("No Figma preview tiles were captured");
  }

  if (tiles.length === 1) {
    await page.screenshot({ path: outputPath, clip });
    return { tileCount: 1, overlapPx: 0 };
  }

  for (let index = 1; index < tiles.length; index += 1) {
    const previous = tiles[index - 1];
    const current = tiles[index];
    if (typeof previous.scrollY === "number" && typeof current.scrollY === "number") {
      const delta = Math.max(0, current.scrollY - previous.scrollY);
      const expectedOverlap = Math.max(24, Math.min(current.height - 1, current.height - delta));
      current.expectedOverlap = expectedOverlap;
      current.overlapTolerance = FIGMA_JOIN_TOLERANCE_PX;
    }
  }

  const stitchResult = await stitchFigmaTiles(browser, outputPath, tiles, effectiveOverlap);
  return {
    tileCount: tiles.length,
    overlapPx: effectiveOverlap,
    resolvedOverlaps: stitchResult?.overlaps || [],
    totalHeight: stitchResult?.totalHeight || null,
  };
}

async function captureFigmaPage(url, outputPath, viewport) {
  const browser = await chromium.launch({ headless: true });
  try {
    let lastError = null;

    for (let attempt = 1; attempt <= FIGMA_CAPTURE_ATTEMPTS; attempt += 1) {
      const page = await browser.newPage({ viewport });
      try {
        console.log(`Opening Figma screenshot URL for ${viewport.width}x${viewport.height} (attempt ${attempt}/${FIGMA_CAPTURE_ATTEMPTS})`);
        await page.goto(url, { waitUntil: "domcontentloaded", timeout: FIGMA_GOTO_TIMEOUT_MS });
        console.log(`Trying cookie consent buttons at ${viewport.width}x${viewport.height}`);
        const clickedCookies = await tryAcceptFigmaCookies(page);
        console.log(clickedCookies ? "Cookie banner dismissed via click" : "No cookie click action applied");
        console.log(`Waiting for Figma canvas readiness at ${viewport.width}x${viewport.height}`);
        const previewTarget = await waitForFigmaPreview(page);
        const clip = previewTarget.clip;
        await hideFigmaHostBanners(page);
        console.log(
          `Capturing Figma full-page preview from ${Math.round(clip.width)}x${Math.round(clip.height)} at ${Math.round(clip.x)},${Math.round(clip.y)}`,
        );
        const result = await captureFigmaFullPage(page, clip, browser, outputPath);
        const overlapSummary = Array.isArray(result.resolvedOverlaps)
          ? result.resolvedOverlaps.map((item) => item.overlap).join(", ")
          : "";
        console.log(
          `Figma full-page screenshot completed with ${result.tileCount} tile(s)${overlapSummary ? `; resolved overlaps: ${overlapSummary}` : ""}`,
        );
        await page.close();
        return;
      } catch (error) {
        lastError = error;
        await saveFigmaDebugArtifacts(page, outputPath, `attempt-${attempt}-failure`).catch(() => null);
        console.error(`Figma capture attempt ${attempt} failed: ${error.message}`);
        await page.close().catch(() => null);
      }
    }

    throw lastError || new Error("Unknown Figma screenshot capture failure");
  } finally {
    await browser.close();
  }
}

async function captureGenericPage(url, outputPath, viewport) {
  const browser = await chromium.launch({ headless: true });
  try {
    const page = await browser.newPage({ viewport });
    console.log(`Opening page screenshot URL for ${viewport.width}x${viewport.height}`);
    await page.goto(url, { waitUntil: "networkidle", timeout: 60000 });
    await page.screenshot({ path: outputPath, fullPage: true });
  } finally {
    await browser.close();
  }
}

export async function runFigmaScreenshots(argv = process.argv.slice(2)) {
  const args = parseCliArgs(argv);
  const mode = getArg(args, "mode", "both");
  const variant = getArg(args, "variant", "draft");
  const failFast = hasFlag(args, "fail-fast");
  const figmaConfigPath = getArg(args, "figma-config", DEFAULT_FIGMA_CONFIG);
  const figmaConfig = await loadFigmaConfig(figmaConfigPath);
  const pages = selectPages(figmaConfig, {
    all: hasFlag(args, "all"),
    pageId: getArg(args, "page-id", null),
  });

  console.log(`Starting screenshot capture for ${pages.length} page(s) in mode '${mode}'`);

  for (const page of pages) {
    const pagePaths = await ensurePageStructure(page);
    const manifest = await loadManifest(page.pageSlug);
    const snapshotIndex = [];
    try {
      if (mode === "figma" || mode === "both") {
        if (!page.figmaScreenshotUrl) {
          throw new Error(`Missing figma_screenshot_url for ${page.pageId}`);
        }

        for (const [label, viewport] of Object.entries(VIEWPORTS)) {
          const outputPath = path.join(pagePaths.screenFigma, `${label}.png`);
          console.log(`[${page.pageSlug}] Capturing Figma ${label} screenshot`);
          await captureFigmaPage(page.figmaScreenshotUrl, outputPath, viewport);
          snapshotIndex.push({
            type: "figma",
            viewport: label,
            file: path.relative(process.cwd(), outputPath),
          });
        }

        manifest.status.figma_screenshots_ready = true;
      }

      if (mode === "wp" || mode === "both") {
        if (!page.siteUrl) {
          throw new Error(`Missing site_url for ${page.pageId}`);
        }

        for (const [label, viewport] of Object.entries(VIEWPORTS)) {
          const outputPath = path.join(pagePaths.screenWp, `${variant}-${label}.png`);
          console.log(`[${page.pageSlug}] Capturing WordPress ${variant} ${label} screenshot`);
          await captureGenericPage(page.siteUrl, outputPath, viewport);
          snapshotIndex.push({
            type: "wp",
            variant,
            viewport: label,
            file: path.relative(process.cwd(), outputPath),
          });
        }
      }

      await writeJson(path.join(pagePaths.reports, "screenshot-index.json"), {
        page_slug: page.pageSlug,
        mode,
        variant,
        generated_at: nowIso(),
        screenshots: snapshotIndex,
      });

      manifest.last_screenshot_run = {
        mode,
        variant,
        generated_at: nowIso(),
      };
      manifest.source.figma_mcp_url = page.figmaMcpUrl;
      manifest.source.figma_screenshot_url = page.figmaScreenshotUrl;
      manifest.errors = Array.isArray(manifest.errors) ? manifest.errors.filter(Boolean) : [];
      await saveManifest(page.pageSlug, manifest);
      await appendHistory(page.pageSlug, {
        event: "screenshots_generated",
        mode,
        variant,
      });
      await upsertIndexPage(page, manifest.current_stage || "initialized");

      console.log(`Captured screenshots for ${page.pageSlug} (${mode})`);
    } catch (error) {
      manifest.errors = Array.isArray(manifest.errors) ? manifest.errors : [];
      manifest.errors.push(String(error.message));
      manifest.current_stage = "error";
      manifest.last_screenshot_run = {
        mode,
        variant,
        generated_at: nowIso(),
        failed: true,
      };
      await saveManifest(page.pageSlug, manifest);
      await appendHistory(page.pageSlug, {
        event: "screenshot_failed",
        mode,
        variant,
        error: String(error.message),
      });
      await upsertIndexPage(page, "error");

      if (failFast) {
        throw error;
      }

      console.error(`Failed screenshots for ${page.pageSlug}: ${error.message}`);
    }
  }
}

const entrypointPath = new URL(import.meta.url).pathname;
if (process.argv[1] === entrypointPath) {
  runFigmaScreenshots().catch((error) => {
    console.error(error.message);
    process.exitCode = 1;
  });
}
