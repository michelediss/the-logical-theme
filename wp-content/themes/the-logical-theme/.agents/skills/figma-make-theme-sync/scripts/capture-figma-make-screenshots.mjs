#!/usr/bin/env node

import path from 'node:path';
import { chromium } from 'playwright';
import {
  BREAKPOINTS,
  completeCaptureManifest,
  createBreakpointTimeoutMessage,
  createCaptureManifest,
  ensureDir,
  getBreakpointList,
  getRunRoot,
  isPlaceholderFigmaMakeUrl,
  loadFigmaConfig,
  logCaptureProgress,
  parseArgs,
  readCanonicalArg,
  requireCanonicalArg,
  requireRunId,
  resolveFigmaCaptureUrl,
  runWithTimeout,
  sanitizeSegment,
  updateCaptureManifestProgress,
  failCaptureManifest,
  writeJson,
} from './visual-qa-common.mjs';

const BREAKPOINT_TIMEOUT_MS = 90000;

async function main() {
  const args = parseArgs(process.argv);
  const config = loadFigmaConfig();
  const pageKey = readCanonicalArg(args, 'page-key', ['pageKey', 'page_key']);
  const explicitFigmaUrl = readCanonicalArg(args, 'figma-capture-url', ['figma-url', 'figmaUrl', 'figma_capture_url']);
  const figmaUrl = resolveFigmaCaptureUrl({
    explicitUrl: explicitFigmaUrl,
    pageKey,
    config,
  });
  const targetName = requireCanonicalArg(args, 'target-name', ['targetName', 'target_name']);
  const runId = requireRunId(args);
  const artifactRoot = readCanonicalArg(args, 'artifact-root', ['artifactRoot', 'artifact_root']);
  const waitMs = Number(readCanonicalArg(args, 'wait-ms', ['waitMs', 'wait_ms']) || 5000);
  const breakpoints = getBreakpointList(readCanonicalArg(args, 'breakpoints'));

  if (isPlaceholderFigmaMakeUrl(figmaUrl)) {
    throw new Error(`Figma Make URL still uses placeholder APP_ID. Pass --figma-capture-url with a real Make URL or update figma.json.`);
  }

  const runRoot = getRunRoot(targetName, runId, artifactRoot);
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
    } catch (error) {
      failCaptureManifest(manifest, error);
      writeJson(manifestPath, manifest);
      throw error;
    }
  } finally {
    await browser.close();
  }

  process.stdout.write(`${runRoot}\n`);
}

main().catch((error) => {
  logCaptureProgress(`[figma] ${error.message}`);
  process.stderr.write(`${error.message}\n`);
  process.exit(1);
});
