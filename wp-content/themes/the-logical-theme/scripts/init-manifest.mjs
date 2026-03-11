#!/usr/bin/env node

import {
  DEFAULT_FIGMA_CONFIG,
  ensurePageStructure,
  getArg,
  hasFlag,
  loadFigmaConfig,
  loadManifest,
  parseCliArgs,
  saveManifest,
  selectPages,
  upsertIndexPage,
} from "./lib/ai-source-utils.mjs";

export async function runInitManifest(argv = process.argv.slice(2)) {
  const args = parseCliArgs(argv);
  const figmaConfigPath = getArg(args, "figma-config", DEFAULT_FIGMA_CONFIG);
  const figmaConfig = await loadFigmaConfig(figmaConfigPath);
  const pages = selectPages(figmaConfig, {
    all: hasFlag(args, "all"),
    pageId: getArg(args, "page-id", null),
  });

  for (const page of pages) {
    await ensurePageStructure(page);
    const currentManifest = await loadManifest(page.pageSlug);
    currentManifest.page_title = page.pageTitle;
    currentManifest.local_url = page.siteUrl;
    currentManifest.template_hint = page.wpTemplate;
    currentManifest.source.figma_entry_id = page.pageId;
    currentManifest.source.figma_mcp_url = page.figmaMcpUrl;
    currentManifest.source.figma_screenshot_url = page.figmaScreenshotUrl;
    currentManifest.status.manifest_initialized = true;
    currentManifest.current_stage = currentManifest.status.figma_ingested ? "ingested" : "initialized";
    await saveManifest(page.pageSlug, currentManifest);
    await upsertIndexPage(page, currentManifest.current_stage);
    console.log(`Initialized manifest for ${page.pageSlug}`);
  }
}

const entrypointPath = new URL(import.meta.url).pathname;
if (process.argv[1] === entrypointPath) {
  runInitManifest().catch((error) => {
    console.error(error.message);
    process.exitCode = 1;
  });
}
