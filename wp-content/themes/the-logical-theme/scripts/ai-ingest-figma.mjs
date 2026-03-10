#!/usr/bin/env node

import { runFigmaScreenshots } from "./figma-screenshots.mjs";
import { runIngestFigma } from "./ingest-figma.mjs";
import { runInitManifest } from "./init-manifest.mjs";
import { getArg, hasFlag, parseCliArgs } from "./lib/ai-source-utils.mjs";

export async function runAiIngestFigma(argv = process.argv.slice(2)) {
  const args = parseCliArgs(argv);
  const sharedArgs = [];

  if (hasFlag(args, "all")) {
    sharedArgs.push("--all");
  }

  const pageId = getArg(args, "page-id", null);
  if (pageId) {
    sharedArgs.push("--page-id", pageId);
  }

  const figmaConfig = getArg(args, "figma-config", null);
  if (figmaConfig) {
    sharedArgs.push("--figma-config", figmaConfig);
  }

  const dumpRoot = getArg(args, "source-dump-root", null);
  const metadataOnly = hasFlag(args, "allow-metadata-only");

  await runInitManifest(sharedArgs);

  const ingestArgs = [...sharedArgs];
  if (dumpRoot) {
    ingestArgs.push("--source-dump-root", dumpRoot);
  }
  if (metadataOnly) {
    ingestArgs.push("--allow-metadata-only");
  }
  await runIngestFigma(ingestArgs);

  const screenshotArgs = [...sharedArgs, "--mode", "figma"];
  await runFigmaScreenshots(screenshotArgs);
}

const entrypointPath = new URL(import.meta.url).pathname;
if (process.argv[1] === entrypointPath) {
  runAiIngestFigma().catch((error) => {
    console.error(error.message);
    process.exitCode = 1;
  });
}
