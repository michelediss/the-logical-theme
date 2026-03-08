#!/usr/bin/env node

import path from 'node:path';
import { pathToFileURL } from 'node:url';
import { chromium } from 'playwright';
import {
  BREAKPOINTS,
  completeCaptureManifest,
  createBreakpointTimeoutMessage,
  createCaptureManifest,
  ensureDir,
  ensureRunManifest,
  failCaptureManifest,
  getBreakpointList,
  getRunRoot,
  loadFigmaConfig,
  logCaptureProgress,
  parseArgs,
  parseIterationValue,
  readCanonicalArg,
  requireCanonicalArg,
  requireRunId,
  resolveSiteCaptureUrl,
  runWithTimeout,
  sanitizeSegment,
  setCurrentIteration,
  updateCaptureManifestProgress,
  writeJson,
  writeRunManifest,
} from './visual-qa-common.mjs';

const BREAKPOINT_TIMEOUT_MS = 90000;

export async function runWordPressCapture({
  targetName,
  runId,
  artifactRoot,
  pageKey = null,
  previewUrl,
  waitMs = 2000,
  breakpoints,
  iteration = 1,
  config = loadFigmaConfig(),
}) {
  const pageUrl = resolveSiteCaptureUrl({
    explicitUrl: previewUrl,
    pageKey,
    config,
  });
  const normalizedIteration = parseIterationValue(iteration, 1);
  const runRoot = getRunRoot(targetName, runId, artifactRoot);
  const runManifest = ensureRunManifest(runRoot, { targetName, runId });
  const outputDir = ensureDir(path.join(runRoot, 'output', `iter-${normalizedIteration}`));
  const manifestPath = path.join(runRoot, 'output', `iter-${normalizedIteration}`, 'manifest.json');
  const manifest = createCaptureManifest({
    targetName,
    pageKey: pageKey || null,
    runId,
    runRoot,
    kind: 'wordpress',
    source: pageUrl,
    breakpoints,
    iteration: normalizedIteration,
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
      setCurrentIteration(runManifest, normalizedIteration);
      writeRunManifest(runManifest);
    } catch (error) {
      failCaptureManifest(manifest, error);
      writeJson(manifestPath, manifest);
      throw error;
    }
  } finally {
    await browser.close();
  }

  return {
    runRoot,
    outputDir: path.join(runRoot, 'output', `iter-${normalizedIteration}`),
    manifestPath,
    pageUrl,
    iteration: normalizedIteration,
  };
}

async function main() {
  const args = parseArgs(process.argv);
  const config = loadFigmaConfig();
  const pageKey = readCanonicalArg(args, 'page-key', ['pageKey', 'page_key']);
  const previewUrl = readCanonicalArg(args, 'preview-url', ['url', 'previewUrl', 'preview_url']);
  const targetName = requireCanonicalArg(args, 'target-name', ['targetName', 'target_name']);
  const runId = requireRunId(args);
  const artifactRoot = readCanonicalArg(args, 'artifact-root', ['artifactRoot', 'artifact_root']);
  const iteration = parseIterationValue(readCanonicalArg(args, 'iteration'), 1);
  const waitMs = Number(readCanonicalArg(args, 'wait-ms', ['waitMs', 'wait_ms']) || 2000);
  const breakpoints = getBreakpointList(readCanonicalArg(args, 'breakpoints'));
  const result = await runWordPressCapture({
    targetName,
    runId,
    artifactRoot,
    pageKey,
    previewUrl,
    waitMs,
    breakpoints,
    iteration,
    config,
  });

  process.stdout.write(`${result.outputDir}\n`);
}

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) {
  main().catch((error) => {
    logCaptureProgress(`[wordpress] ${error.message}`);
    process.stderr.write(`${error.message}\n`);
    process.exit(1);
  });
}
