#!/usr/bin/env node

import path from 'node:path';
import { chromium } from 'playwright';
import {
  BREAKPOINTS,
  completeCaptureManifest,
  createBreakpointTimeoutMessage,
  createCaptureManifest,
  ensureDir,
  failCaptureManifest,
  getBreakpointList,
  getRunRoot,
  loadFigmaConfig,
  logCaptureProgress,
  parseArgs,
  readCanonicalArg,
  requireCanonicalArg,
  requireRunId,
  resolveSiteCaptureUrl,
  runWithTimeout,
  sanitizeSegment,
  updateCaptureManifestProgress,
  writeJson,
} from './visual-qa-common.mjs';

const BREAKPOINT_TIMEOUT_MS = 90000;

async function main() {
  const args = parseArgs(process.argv);
  const config = loadFigmaConfig();
  const pageKey = readCanonicalArg(args, 'page-key', ['pageKey', 'page_key']);
  const previewUrl = readCanonicalArg(args, 'preview-url', ['url', 'previewUrl', 'preview_url']);
  const pageUrl = resolveSiteCaptureUrl({
    explicitUrl: previewUrl,
    pageKey,
    config,
  });
  const targetName = requireCanonicalArg(args, 'target-name', ['targetName', 'target_name']);
  const runId = requireRunId(args);
  const artifactRoot = readCanonicalArg(args, 'artifact-root', ['artifactRoot', 'artifact_root']);
  const iteration = Number(readCanonicalArg(args, 'iteration') || 1);
  const waitMs = Number(readCanonicalArg(args, 'wait-ms', ['waitMs', 'wait_ms']) || 2000);
  const breakpoints = getBreakpointList(readCanonicalArg(args, 'breakpoints'));

  if (!Number.isInteger(iteration) || iteration < 1 || iteration > 3) {
    throw new Error('Iteration must be an integer between 1 and 3');
  }

  const runRoot = getRunRoot(targetName, runId, artifactRoot);
  const outputDir = ensureDir(path.join(runRoot, 'output', `iter-${iteration}`));
  const manifestPath = path.join(runRoot, 'output', `iter-${iteration}`, 'manifest.json');
  const manifest = createCaptureManifest({
    targetName,
    pageKey: pageKey || null,
    runId,
    runRoot,
    kind: 'wordpress',
    source: pageUrl,
    breakpoints,
    iteration,
  });
  const browser = await chromium.launch({ headless: true });

  writeJson(manifestPath, manifest);

  try {
    try {
      for (const breakpoint of breakpoints) {
        const viewport = BREAKPOINTS[breakpoint];
        const context = await browser.newContext({ viewport });
        updateCaptureManifestProgress(manifest, breakpoint);
        writeJson(manifestPath, manifest);
        logCaptureProgress(`[wordpress] starting breakpoint ${breakpoint} (${viewport.width}x${viewport.height}) -> ${pageUrl}`);

        try {
          const capture = await runWithTimeout(async () => {
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

            return {
              breakpoint,
              viewport,
              pageKey: pageKey || null,
              source: pageUrl,
              screenshot: screenshotPath,
            };
          }, BREAKPOINT_TIMEOUT_MS, createBreakpointTimeoutMessage('WordPress', breakpoint, BREAKPOINT_TIMEOUT_MS));

          updateCaptureManifestProgress(manifest, breakpoint, capture);
          writeJson(manifestPath, manifest);
          logCaptureProgress(`[wordpress] completed breakpoint ${breakpoint}`);
        } finally {
          await context.close();
        }
      }

      completeCaptureManifest(manifest);
      writeJson(manifestPath, manifest);
    } catch (error) {
      failCaptureManifest(manifest, error);
      writeJson(manifestPath, manifest);
      throw error;
    }
  } finally {
    await browser.close();
  }

  process.stdout.write(`${path.join(runRoot, 'output', `iter-${iteration}`)}\n`);
}

main().catch((error) => {
  logCaptureProgress(`[wordpress] ${error.message}`);
  process.stderr.write(`${error.message}\n`);
  process.exit(1);
});
