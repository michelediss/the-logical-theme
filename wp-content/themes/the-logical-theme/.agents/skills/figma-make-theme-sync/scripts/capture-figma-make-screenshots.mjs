#!/usr/bin/env node

import path from 'node:path';
import { chromium } from 'playwright';
import {
  BREAKPOINTS,
  ensureDir,
  getBreakpointList,
  getRunRoot,
  loadFigmaConfig,
  parseArgs,
  resolveFigmaCaptureUrl,
  sanitizeSegment,
  writeJson,
} from './visual-qa-common.mjs';

async function main() {
  const args = parseArgs(process.argv);
  const config = loadFigmaConfig();
  const pageKey = args['page-key'] || args.pageKey;
  const figmaUrl = resolveFigmaCaptureUrl({
    explicitUrl: args['figma-url'] || args.figmaUrl,
    pageKey,
    config,
  });
  const targetName = args['target-name'] || args.targetName;
  const runId = args['run-id'] || args.runId;
  const artifactRoot = args['artifact-root'] || args.artifactRoot;
  const waitMs = Number(args['wait-ms'] || args.waitMs || 5000);
  const breakpoints = getBreakpointList(args.breakpoints);

  if (!figmaUrl) {
    throw new Error('Missing required --figma-url');
  }

  if (!targetName) {
    throw new Error('Missing required --target-name');
  }

  const runRoot = getRunRoot(targetName, runId, artifactRoot);
  const outputDir = ensureDir(path.join(runRoot, 'input'));
  const browser = await chromium.launch({ headless: true });
  const captures = [];

  try {
    for (const breakpoint of breakpoints) {
      const viewport = BREAKPOINTS[breakpoint];
      const context = await browser.newContext({ viewport });
      const page = await context.newPage();

      await page.goto(figmaUrl, { waitUntil: 'networkidle', timeout: 120000 });

      if (waitMs > 0) {
        await page.waitForTimeout(waitMs);
      }

      const screenshotPath = path.join(outputDir, `${sanitizeSegment(breakpoint)}.png`);

      await page.screenshot({
        path: screenshotPath,
        fullPage: true,
      });

      captures.push({
        breakpoint,
        viewport,
        pageKey: pageKey || null,
        source: figmaUrl,
        screenshot: screenshotPath,
      });

      await context.close();
    }
  } finally {
    await browser.close();
  }

  writeJson(path.join(runRoot, 'input', 'manifest.json'), {
    targetName,
    pageKey: pageKey || null,
    runRoot,
    captures,
  });

  process.stdout.write(`${runRoot}\n`);
}

main().catch((error) => {
  process.stderr.write(`${error.message}\n`);
  process.exit(1);
});
