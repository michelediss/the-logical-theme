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
  resolveSiteCaptureUrl,
  sanitizeSegment,
  writeJson,
} from './visual-qa-common.mjs';

async function main() {
  const args = parseArgs(process.argv);
  const config = loadFigmaConfig();
  const pageKey = args['page-key'] || args.pageKey;
  const pageUrl = resolveSiteCaptureUrl({
    explicitUrl: args.url,
    pageKey,
    config,
  });
  const targetName = args['target-name'] || args.targetName;
  const runId = args['run-id'] || args.runId;
  const artifactRoot = args['artifact-root'] || args.artifactRoot;
  const iteration = Number(args.iteration || 1);
  const waitMs = Number(args['wait-ms'] || args.waitMs || 2000);
  const breakpoints = getBreakpointList(args.breakpoints);

  if (!pageUrl) {
    throw new Error('Missing required --url');
  }

  if (!targetName) {
    throw new Error('Missing required --target-name');
  }

  if (!Number.isInteger(iteration) || iteration < 1 || iteration > 3) {
    throw new Error('Iteration must be an integer between 1 and 3');
  }

  const runRoot = getRunRoot(targetName, runId, artifactRoot);
  const outputDir = ensureDir(path.join(runRoot, 'output', `iter-${iteration}`));
  const browser = await chromium.launch({ headless: true });
  const captures = [];

  try {
    for (const breakpoint of breakpoints) {
      const viewport = BREAKPOINTS[breakpoint];
      const context = await browser.newContext({ viewport });
      const page = await context.newPage();

      await page.goto(pageUrl, { waitUntil: 'networkidle', timeout: 120000 });

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
        source: pageUrl,
        screenshot: screenshotPath,
      });

      await context.close();
    }
  } finally {
    await browser.close();
  }

  writeJson(path.join(runRoot, 'output', `iter-${iteration}`, 'manifest.json'), {
    targetName,
    pageKey: pageKey || null,
    runRoot,
    iteration,
    captures,
  });

  process.stdout.write(`${path.join(runRoot, 'output', `iter-${iteration}`)}\n`);
}

main().catch((error) => {
  process.stderr.write(`${error.message}\n`);
  process.exit(1);
});
