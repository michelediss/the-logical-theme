#!/usr/bin/env node

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

const FIGMA_READY_TIMEOUT_MS = 90000;
const FIGMA_PROBE_INTERVAL_MS = 5000;

async function tryAcceptFigmaCookies(page) {
  const buttonTexts = [
    "Allow all cookies",
    "Do not allow cookies",
  ];

  for (const text of buttonTexts) {
    const locator = page.getByRole("button", { name: text }).first();
    try {
      if (await locator.isVisible({ timeout: 1000 })) {
        await locator.click({ timeout: 2000 }).catch(() => null);
      }
    } catch {
      // ignore transient UI probes
    }
  }
}

async function waitForFigmaReady(page) {
  const maxAttempts = Math.ceil(FIGMA_READY_TIMEOUT_MS / FIGMA_PROBE_INTERVAL_MS);

  for (let attempt = 1; attempt <= maxAttempts; attempt += 1) {
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

async function captureFigmaPage(url, outputPath, viewport) {
  const browser = await chromium.launch({ headless: true });
  try {
    const page = await browser.newPage({ viewport });
    console.log(`Opening Figma screenshot URL for ${viewport.width}x${viewport.height}`);
    await page.goto(url, { waitUntil: "domcontentloaded", timeout: 60000 });
    console.log(`Trying cookie consent buttons at ${viewport.width}x${viewport.height}`);
    await tryAcceptFigmaCookies(page);
    console.log(`Waiting for Figma canvas readiness at ${viewport.width}x${viewport.height}`);
    const clip = await waitForFigmaReady(page);
    console.log(
      `Capturing Figma preview clip ${Math.round(clip.width)}x${Math.round(clip.height)} at ${Math.round(clip.x)},${Math.round(clip.y)}`,
    );
    await page.screenshot({ path: outputPath, clip });
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
