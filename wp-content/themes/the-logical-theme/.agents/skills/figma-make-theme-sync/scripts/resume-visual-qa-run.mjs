#!/usr/bin/env node

import { pathToFileURL } from 'node:url';
import {
  ensureRunManifest,
  getBreakpointList,
  getNextIteration,
  getRunRoot,
  loadFigmaConfig,
  loadRunManifest,
  parseArgs,
  readCanonicalArg,
  recordResumeEvent,
  requireCanonicalArg,
  requireRunId,
  writeRunManifest,
} from './visual-qa-common.mjs';
import { runFigmaCapture } from './capture-figma-make-screenshots.mjs';
import { runWordPressCapture } from './capture-wp-screenshots.mjs';
import { runLighthouseAudit } from './run-lighthouse-audit.mjs';
import { runVisualQaReport } from './prepare-visual-qa-report.mjs';

function readBaselineMode(args) {
  const value = readCanonicalArg(args, 'baseline-mode') || 'reuse';

  if (!['reuse', 'refresh'].includes(value)) {
    throw new Error('Baseline mode must be reuse or refresh.');
  }

  return value;
}

async function main() {
  const args = parseArgs(process.argv);
  const config = loadFigmaConfig();
  const targetName = requireCanonicalArg(args, 'target-name', ['targetName', 'target_name']);
  const runId = requireRunId(args);
  const artifactRoot = readCanonicalArg(args, 'artifact-root', ['artifactRoot', 'artifact_root']);
  const pageKey = readCanonicalArg(args, 'page-key', ['pageKey', 'page_key']);
  const previewUrl = readCanonicalArg(args, 'preview-url', ['url', 'previewUrl', 'preview_url']);
  const figmaCaptureUrl = readCanonicalArg(args, 'figma-capture-url', ['figmaUrl', 'figma_url', 'figma-url']);
  const waitMs = Number(readCanonicalArg(args, 'wait-ms', ['waitMs', 'wait_ms']) || 2000);
  const figmaWaitMs = Number(readCanonicalArg(args, 'figma-wait-ms', ['figmaWaitMs', 'figma_wait_ms']) || 5000);
  const breakpoints = getBreakpointList(readCanonicalArg(args, 'breakpoints'));
  const finalStatus = readCanonicalArg(args, 'final-status', ['finalStatus', 'final_status']) || 'pending_review';
  const appliedFixes = String(readCanonicalArg(args, 'applied-fixes', ['appliedFixes', 'applied_fixes']) || '')
    .split(',')
    .map((item) => item.trim())
    .filter(Boolean);
  const resumeNote = readCanonicalArg(args, 'resume-note', ['resumeNote', 'resume_note']) || null;
  const baselineMode = readBaselineMode(args);
  const runRoot = getRunRoot(targetName, runId, artifactRoot);
  const runManifest = ensureRunManifest(runRoot, { targetName, runId });
  const iteration = getNextIteration(runManifest);

  if (baselineMode === 'reuse' && runManifest.baseline_history.length === 0) {
    throw new Error('Cannot reuse baseline because this run has no recorded Figma baseline. Use --baseline-mode refresh or start a new run.');
  }

  if (baselineMode === 'refresh') {
    await runFigmaCapture({
      targetName,
      runId,
      artifactRoot,
      pageKey,
      explicitFigmaUrl: figmaCaptureUrl,
      waitMs: figmaWaitMs,
      breakpoints,
      config,
      baselineMode: 'refresh',
      refreshedBeforeIteration: iteration,
      note: resumeNote,
      designContextRefreshed: true,
    });
  }

  const updatedRunManifest = baselineMode === 'refresh'
    ? loadRunManifest(runRoot, { targetName, runId })
    : runManifest;

  recordResumeEvent(updatedRunManifest, {
    iteration,
    baselineMode,
    note: resumeNote,
    baselineGeneration: updatedRunManifest.baseline_generation || 1,
    designContextRefreshRequired: baselineMode === 'refresh',
  });
  writeRunManifest(updatedRunManifest);

  await runWordPressCapture({
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

  await runLighthouseAudit({
    targetName,
    runId,
    artifactRoot,
    pageKey,
    previewUrl,
    iteration,
    appliedFixes,
    config,
  });

  const report = await runVisualQaReport({
    targetName,
    runId,
    artifactRoot,
    iteration,
    breakpoints,
    finalStatus,
  });

  process.stdout.write(`${report.jsonPath}\n`);
}

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) {
  main().catch((error) => {
    process.stderr.write(`${error.message}\n`);
    process.exit(1);
  });
}
