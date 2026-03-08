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
  ensureInitialBaseline,
  ensureRunManifest,
  getBreakpointList,
  getRunRoot,
  isPlaceholderFigmaMakeUrl,
  loadFigmaConfig,
  logCaptureProgress,
  markBaselineCaptureComplete,
  parseArgs,
  parseIterationValue,
  readCanonicalArg,
  recordBaselineSnapshot,
  requireCanonicalArg,
  requireRunId,
  resolveFigmaCaptureUrl,
  runWithTimeout,
  sanitizeSegment,
  updateCaptureManifestProgress,
  failCaptureManifest,
  writeJson,
  writeRunManifest,
} from './visual-qa-common.mjs';

const BREAKPOINT_TIMEOUT_MS = 90000;

function validateBaselineMode(value) {
  if (!['initial', 'refresh'].includes(value)) {
    throw new Error('Baseline mode must be initial or refresh.');
  }
}

export async function runFigmaCapture({
  targetName,
  runId,
  artifactRoot,
  pageKey = null,
  explicitFigmaUrl,
  waitMs = 5000,
  breakpoints,
  config = loadFigmaConfig(),
  baselineMode = 'initial',
  refreshedBeforeIteration = 1,
  note = null,
  designContextRefreshed = false,
}) {
  validateBaselineMode(baselineMode);
  const figmaUrl = resolveFigmaCaptureUrl({
    explicitUrl: explicitFigmaUrl,
    pageKey,
    config,
  });
  if (isPlaceholderFigmaMakeUrl(figmaUrl)) {
    throw new Error(`Figma Make URL still uses placeholder APP_ID. Pass --figma-capture-url with a real Make URL or update figma.json.`);
  }

  const runRoot = getRunRoot(targetName, runId, artifactRoot);
  const runManifest = ensureRunManifest(runRoot, { targetName, runId });
  const normalizedBaselineIteration = parseIterationValue(refreshedBeforeIteration, 1);
  if (baselineMode === 'refresh') {
    recordBaselineSnapshot(runManifest, {
      mode: 'refresh',
      refreshedBeforeIteration: normalizedBaselineIteration,
      pageKey,
      figmaUrl,
      note,
      figmaCaptureCompleted: false,
      designContextRefreshed,
    });
  } else {
    ensureInitialBaseline(runManifest, {
      pageKey,
      figmaUrl,
    });
  }
  writeRunManifest(runManifest);
  const outputDir = ensureDir(path.join(runRoot, 'input'));
  const manifestPath = path.join(runRoot, 'input', 'manifest.json');
  const manifest = createCaptureManifest({
    targetName,
    pageKey: pageKey || null,
    runId,
    runRoot,
    kind: 'figma',
    source: figmaUrl,
    breakpoints,
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
        logCaptureProgress(`[figma] starting breakpoint ${breakpoint} (${viewport.width}x${viewport.height}) -> ${figmaUrl}`);

        try {
          const capture = await runWithTimeout(async () => {
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

            return {
              breakpoint,
              viewport,
              pageKey: pageKey || null,
              source: figmaUrl,
              screenshot: screenshotPath,
            };
          }, BREAKPOINT_TIMEOUT_MS, createBreakpointTimeoutMessage('Figma', breakpoint, BREAKPOINT_TIMEOUT_MS));

          updateCaptureManifestProgress(manifest, breakpoint, capture);
          writeJson(manifestPath, manifest);
          logCaptureProgress(`[figma] completed breakpoint ${breakpoint}`);
        } finally {
          await context.close();
        }
      }

      completeCaptureManifest(manifest);
      writeJson(manifestPath, manifest);
      markBaselineCaptureComplete(runManifest, {
        figmaCaptureCompleted: true,
        designContextRefreshed,
        pageKey,
        figmaUrl,
        note,
      });
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
    manifestPath,
    figmaUrl,
    baselineMode,
    refreshedBeforeIteration: normalizedBaselineIteration,
  };
}

async function main() {
  const args = parseArgs(process.argv);
  const config = loadFigmaConfig();
  const pageKey = readCanonicalArg(args, 'page-key', ['pageKey', 'page_key']);
  const explicitFigmaUrl = readCanonicalArg(args, 'figma-capture-url', ['figma-url', 'figmaUrl', 'figma_capture_url']);
  const targetName = requireCanonicalArg(args, 'target-name', ['targetName', 'target_name']);
  const runId = requireRunId(args);
  const artifactRoot = readCanonicalArg(args, 'artifact-root', ['artifactRoot', 'artifact_root']);
  const waitMs = Number(readCanonicalArg(args, 'wait-ms', ['waitMs', 'wait_ms']) || 5000);
  const breakpoints = getBreakpointList(readCanonicalArg(args, 'breakpoints'));
  const baselineMode = readCanonicalArg(args, 'baseline-mode') || 'initial';
  const refreshedBeforeIteration = parseIterationValue(readCanonicalArg(args, 'refreshed-before-iteration'), 1);
  const note = readCanonicalArg(args, 'note') || null;
  const designContextRefreshed = readCanonicalArg(args, 'design-context-refreshed') === 'true';
  const result = await runFigmaCapture({
    targetName,
    runId,
    artifactRoot,
    pageKey,
    explicitFigmaUrl,
    waitMs,
    breakpoints,
    config,
    baselineMode,
    refreshedBeforeIteration,
    note,
    designContextRefreshed,
  });

  process.stdout.write(`${result.runRoot}\n`);
}

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) {
  main().catch((error) => {
    logCaptureProgress(`[figma] ${error.message}`);
    process.stderr.write(`${error.message}\n`);
    process.exit(1);
  });
}
