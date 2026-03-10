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

async function capturePage(url, outputPath, viewport) {
  const browser = await chromium.launch({ headless: true });
  try {
    const page = await browser.newPage({ viewport });
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
  const figmaConfigPath = getArg(args, "figma-config", DEFAULT_FIGMA_CONFIG);
  const figmaConfig = await loadFigmaConfig(figmaConfigPath);
  const pages = selectPages(figmaConfig, {
    all: hasFlag(args, "all"),
    pageId: getArg(args, "page-id", null),
  });

  for (const page of pages) {
    const pagePaths = await ensurePageStructure(page);
    const manifest = await loadManifest(page.pageSlug);
    const snapshotIndex = [];

    if (mode === "figma" || mode === "both") {
      if (!page.figmaUrl) {
        throw new Error(`Missing figma_url for ${page.pageId}`);
      }

      for (const [label, viewport] of Object.entries(VIEWPORTS)) {
        const outputPath = path.join(pagePaths.screenFigma, `${label}.png`);
        await capturePage(page.figmaUrl, outputPath, viewport);
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
        await capturePage(page.siteUrl, outputPath, viewport);
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
    await saveManifest(page.pageSlug, manifest);
    await appendHistory(page.pageSlug, {
      event: "screenshots_generated",
      mode,
      variant,
    });
    await upsertIndexPage(page, manifest.current_stage || "initialized");

    console.log(`Captured screenshots for ${page.pageSlug} (${mode})`);
  }
}

const entrypointPath = new URL(import.meta.url).pathname;
if (process.argv[1] === entrypointPath) {
  runFigmaScreenshots().catch((error) => {
    console.error(error.message);
    process.exitCode = 1;
  });
}
