#!/usr/bin/env node

import fs from "node:fs/promises";
import path from "node:path";
import {
  DEFAULT_FIGMA_CONFIG,
  appendHistory,
  cleanDir,
  copyPath,
  ensurePageStructure,
  fileExists,
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

async function resolveDumpSource(page, dumpRoot) {
  if (!dumpRoot) {
    return null;
  }

  const candidates = [
    path.join(dumpRoot, `${page.pageId}.json`),
    path.join(dumpRoot, `${page.pageSlug}.json`),
    path.join(dumpRoot, page.pageId),
    path.join(dumpRoot, page.pageSlug),
  ];

  for (const candidate of candidates) {
    if (await fileExists(candidate)) {
      return candidate;
    }
  }

  return null;
}

export async function runIngestFigma(argv = process.argv.slice(2)) {
  const args = parseCliArgs(argv);
  const figmaConfigPath = getArg(args, "figma-config", DEFAULT_FIGMA_CONFIG);
  const dumpRoot = getArg(args, "source-dump-root", null);
  const metadataOnly = hasFlag(args, "allow-metadata-only");
  const figmaConfig = await loadFigmaConfig(figmaConfigPath);
  const pages = selectPages(figmaConfig, {
    all: hasFlag(args, "all"),
    pageId: getArg(args, "page-id", null),
  });

  for (const page of pages) {
    const pagePaths = await ensurePageStructure(page);
    await cleanDir(pagePaths.figmaRawCode);

    const resolvedDump = await resolveDumpSource(page, dumpRoot);
    const ingestSummary = {
      page_id: page.pageId,
      page_slug: page.pageSlug,
      figma_url: page.figmaUrl,
      app_url: page.appUrl,
      source_dump: resolvedDump ? path.relative(process.cwd(), resolvedDump) : null,
      metadata_only: resolvedDump ? false : true,
      ingested_at: nowIso(),
    };

    await writeJson(path.join(pagePaths.figmaRawCode, "source-page.json"), page.rawConfig);
    await writeJson(path.join(pagePaths.figmaRawCode, "ingest-summary.json"), ingestSummary);

    if (resolvedDump) {
      const targetPath = path.join(pagePaths.figmaRawCode, path.basename(resolvedDump));
      await copyPath(resolvedDump, targetPath);
    } else if (!metadataOnly) {
      throw new Error(
        `No MCP dump found for ${page.pageId}. Pass --source-dump-root <dir> or use --allow-metadata-only.`,
      );
    }

    const manifest = await loadManifest(page.pageSlug);
    manifest.status.figma_ingested = true;
    manifest.current_stage = "ingested";
    manifest.source.figma_input_version = ingestSummary.ingested_at;
    manifest.warnings = Array.isArray(manifest.warnings) ? manifest.warnings : [];
    if (!resolvedDump) {
      manifest.warnings.push("figma ingest completed in metadata-only mode");
    }
    await saveManifest(page.pageSlug, manifest);
    await appendHistory(page.pageSlug, {
      event: "figma_ingested",
      source_dump: ingestSummary.source_dump,
      metadata_only: ingestSummary.metadata_only,
    });
    await upsertIndexPage(page, "ingested");

    const files = await fs.readdir(pagePaths.figmaRawCode);
    console.log(`Ingested ${page.pageSlug}: ${files.length} artifact(s)`);
  }
}

const entrypointPath = new URL(import.meta.url).pathname;
if (process.argv[1] === entrypointPath) {
  runIngestFigma().catch((error) => {
    console.error(error.message);
    process.exitCode = 1;
  });
}
