#!/usr/bin/env node

import fs from "node:fs/promises";
import path from "node:path";
import {
  DEFAULT_FIGMA_CONFIG,
  appendHistory,
  cleanDir,
  ensurePageStructure,
  getArg,
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
import { discoverMakeResources, fetchMakeResources } from "./lib/figma-mcp-codex.mjs";

async function writeFetchedResource(pagePaths, resource, payload) {
  const baseDir =
    resource.kind === "source"
      ? path.join(pagePaths.figmaRawCode, "source")
      : resource.kind === "image"
        ? path.join(pagePaths.figmaRawCode, "assets")
        : path.join(pagePaths.figmaRawCode, "docs");
  const targetPath = path.join(baseDir, resource.relativePath);

  await fs.mkdir(path.dirname(targetPath), { recursive: true });
  if (payload.encoding === "base64") {
    await fs.writeFile(targetPath, Buffer.from(payload.content, "base64"));
  } else if (payload.encoding === "text") {
    await fs.writeFile(targetPath, payload.content, "utf8");
  } else {
    await fs.writeFile(targetPath, "", "utf8");
  }

  return targetPath;
}

export async function runIngestFigma(argv = process.argv.slice(2)) {
  const args = parseCliArgs(argv);
  const figmaConfigPath = getArg(args, "figma-config", DEFAULT_FIGMA_CONFIG);
  const failFast = hasFlag(args, "fail-fast");
  const batchSize = Number.parseInt(getArg(args, "batch-size", "12"), 10);
  const limit = Number.parseInt(getArg(args, "limit", "0"), 10);
  const figmaConfig = await loadFigmaConfig(figmaConfigPath);
  const pages = selectPages(figmaConfig, {
    all: hasFlag(args, "all"),
    pageId: getArg(args, "page-id", null),
  });

  console.log(`Starting Figma ingest for ${pages.length} page(s)`);

  for (const page of pages) {
    const pagePaths = await ensurePageStructure(page);
    await cleanDir(pagePaths.figmaRawCode);
    const startedAt = nowIso();
    console.log(`[${page.pageSlug}] Preparing figma-raw workspace`);

    const sourcePagePath = path.join(pagePaths.figmaRawCode, "meta", "source-page.json");
    await writeJson(sourcePagePath, page.rawConfig);

    try {
      if (!page.figmaMcpUrl) {
        throw new Error(`Missing figma_mcp_url for ${page.pageId}`);
      }

      console.log(`[${page.pageSlug}] Discovering MCP resources`);
      const discovered = await discoverMakeResources(page);
      const resourcesToFetch = limit > 0 ? discovered.resources.slice(0, limit) : discovered.resources;
      const savedResources = [];
      console.log(
        `[${page.pageSlug}] Discovered ${discovered.resources.length} resource(s), fetching ${resourcesToFetch.length} with batch size ${batchSize}`,
      );

      for (let index = 0; index < resourcesToFetch.length; index += batchSize) {
        const batch = resourcesToFetch.slice(index, index + batchSize);
        const batchStart = index + 1;
        const batchEnd = index + batch.length;
        console.log(`[${page.pageSlug}] Fetching batch ${batchStart}-${batchEnd}/${resourcesToFetch.length}`);
        const fetchedBatch = await fetchMakeResources(
          batch.map((resource) => resource.uri),
          {
            log: (message) => console.log(`[${page.pageSlug}] ${message}`),
          },
        );

        for (const resource of batch) {
          const payload = fetchedBatch.get(resource.uri);
          if (!payload) {
            throw new Error(`Missing fetched payload for ${resource.uri}`);
          }

          const savedPath = await writeFetchedResource(pagePaths, resource, payload);
          savedResources.push({
            kind: resource.kind,
            uri: resource.uri,
            mimeType: payload.mimeType,
            encoding: payload.encoding,
            relativePath: path.relative(pagePaths.figmaRawCode, savedPath),
          });
          console.log(`[${page.pageSlug}] Saved ${resource.kind} -> ${path.relative(pagePaths.figmaRawCode, savedPath)}`);
        }
      }

      const ingestSummary = {
        page_id: page.pageId,
        page_slug: page.pageSlug,
        figma_mcp_url: page.figmaMcpUrl,
        app_url: page.appUrl,
        file_key: discovered.fileKey,
        fetched_at: nowIso(),
        resource_count: savedResources.length,
        total_discovered_resources: discovered.resources.length,
        resources: savedResources,
        notes: discovered.notes,
      };

      await writeJson(path.join(pagePaths.figmaRawCode, "meta", "resource-index.json"), ingestSummary);

      const manifest = await loadManifest(page.pageSlug);
      manifest.status.figma_ingested = true;
      manifest.current_stage = "ingested";
      manifest.source.figma_input_version = ingestSummary.fetched_at;
      manifest.source.figma_mcp_url = page.figmaMcpUrl;
      manifest.source.figma_screenshot_url = page.figmaScreenshotUrl;
      manifest.warnings = Array.isArray(manifest.warnings)
        ? manifest.warnings.filter((warning) => warning !== "figma ingest completed in metadata-only mode")
        : [];
      manifest.last_ingest_run = {
        started_at: startedAt,
        completed_at: nowIso(),
        file_key: discovered.fileKey,
        resource_count: savedResources.length,
      };
      manifest.errors = [];
      await saveManifest(page.pageSlug, manifest);
      await appendHistory(page.pageSlug, {
        event: "figma_ingested",
        file_key: discovered.fileKey,
        resource_count: savedResources.length,
      });
      await upsertIndexPage(page, "ingested");

      console.log(`Ingested ${page.pageSlug}: ${savedResources.length} resource(s)`);
    } catch (error) {
      const manifest = await loadManifest(page.pageSlug);
      manifest.errors = Array.isArray(manifest.errors) ? manifest.errors : [];
      manifest.errors.push(String(error.message));
      manifest.current_stage = "error";
      manifest.last_ingest_run = {
        started_at: startedAt,
        completed_at: nowIso(),
        failed: true,
      };
      await saveManifest(page.pageSlug, manifest);
      await appendHistory(page.pageSlug, {
        event: "figma_ingest_failed",
        error: String(error.message),
      });
      await upsertIndexPage(page, "error");

      if (failFast) {
        throw error;
      }

      console.error(`Failed ingest for ${page.pageSlug}: ${error.message}`);
    }
  }
}

const entrypointPath = new URL(import.meta.url).pathname;
if (process.argv[1] === entrypointPath) {
  runIngestFigma().catch((error) => {
    console.error(error.message);
    process.exitCode = 1;
  });
}
