#!/usr/bin/env node

import crypto from "node:crypto";
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

function hashBuffer(buffer) {
  return crypto.createHash("sha1").update(buffer).digest("hex");
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
      if (!text || !matchesText(text)) {
        continue;
      }

      let target = node;
      while (target.parentElement) {
        const parent = target.parentElement;
        const style = window.getComputedStyle(parent);
        const rect = parent.getBoundingClientRect();
        const fixedLike = style.position === "fixed" || style.position === "sticky";
        const overlaysBottom = rect.bottom >= window.innerHeight - 4;
        const overlaysTop = rect.top <= 12 && rect.height <= 100;
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

  for (let attempt = 1; attempt <= maxAttempts; attempt += 1) {
    if (attempt === 1 || attempt % 4 === 0) {
      await tryAcceptFigmaCookies(page);
      await hideFigmaHostBanners(page);
    }

    await page.waitForTimeout(FIGMA_PROBE_INTERVAL_MS);

    const preview = await page.evaluate(() => {
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
        })
        .filter((frame) => frame.width > 0 && frame.height > 0);

      previewFrames.sort((left, right) => right.width * right.height - left.width * left.height);
      return previewFrames[0] || null;
    });

    const elapsedSeconds = attempt * (FIGMA_PROBE_INTERVAL_MS / 1000);
    if (preview && preview.width >= 200 && preview.height >= 200) {
      const viewportSize = page.viewportSize();
      const padding = 8;
      const x = Math.max(0, Math.min(preview.x, (viewportSize?.width ?? preview.width) - padding));
      const y = Math.max(0, Math.min(preview.y, (viewportSize?.height ?? preview.height) - padding));
      const width = Math.max(1, Math.min(preview.width - padding * 2, (viewportSize?.width ?? preview.width) - x));
      const height = Math.max(1, Math.min(preview.height - padding * 2, (viewportSize?.height ?? preview.height) - y));
      console.log(`Found Figma preview iframe after ${elapsedSeconds}s`);
      await page.waitForTimeout(1000);
      return { x, y, width, height };
    }

    console.log(`Still waiting for Figma preview iframe: ${elapsedSeconds}s elapsed`);
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

async function stitchFigmaTiles(browser, outputPath, tiles, overlapPx) {
  const width = tiles[0].width;
  const totalHeight = tiles.reduce((sum, tile, index) => {
    if (index === 0) {
      return sum + tile.height;
    }
    return sum + Math.max(1, tile.height - overlapPx);
  }, 0);

  const composePage = await browser.newPage({
    viewport: { width, height: Math.min(1400, Math.max(1, totalHeight)) },
  });

  try {
    await composePage.setContent(
      "<!doctype html><html><head><style>html,body{margin:0;padding:0;background:#fff;}canvas{display:block;}</style></head><body><canvas id='out'></canvas></body></html>",
      { waitUntil: "domcontentloaded" },
    );

    await composePage.evaluate(
      async ({ width: canvasWidth, totalHeight: canvasHeight, overlapPx: overlap, tiles: encodedTiles }) => {
        const canvas = document.getElementById("out");
        if (!(canvas instanceof HTMLCanvasElement)) {
          throw new Error("Missing output canvas");
        }

        canvas.width = canvasWidth;
        canvas.height = canvasHeight;
        const ctx = canvas.getContext("2d");
        if (!ctx) {
          throw new Error("Missing 2D canvas context");
        }

        const loadImage = (dataUrl) =>
          new Promise((resolve, reject) => {
            const image = new Image();
            image.onload = () => resolve(image);
            image.onerror = () => reject(new Error("Failed to load stitched tile image"));
            image.src = dataUrl;
          });

        let offsetY = 0;
        for (let index = 0; index < encodedTiles.length; index += 1) {
          const tile = encodedTiles[index];
          const image = await loadImage(tile.dataUrl);
          ctx.drawImage(image, 0, offsetY, tile.width, tile.height);
          offsetY += index === 0 ? tile.height : Math.max(1, tile.height - overlap);
        }
      },
      {
        width,
        totalHeight,
        overlapPx,
        tiles: tiles.map((tile) => ({
          width: tile.width,
          height: tile.height,
          dataUrl: `data:image/png;base64,${tile.buffer.toString("base64")}`,
        })),
      },
    );

    await composePage.locator("#out").screenshot({ path: outputPath });
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

  await stitchFigmaTiles(browser, outputPath, tiles, effectiveOverlap);
  return { tileCount: tiles.length, overlapPx: effectiveOverlap };
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
        await hideFigmaHostBanners(page);
        console.log(`Waiting for Figma canvas readiness at ${viewport.width}x${viewport.height}`);
        const clip = await waitForFigmaPreview(page);
        console.log(
          `Capturing Figma full-page preview from ${Math.round(clip.width)}x${Math.round(clip.height)} at ${Math.round(clip.x)},${Math.round(clip.y)}`,
        );
        const result = await captureFigmaFullPage(page, clip, browser, outputPath);
        console.log(`Figma full-page screenshot completed with ${result.tileCount} tile(s)`);
        await page.close();
        return;
      } catch (error) {
        lastError = error;
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
