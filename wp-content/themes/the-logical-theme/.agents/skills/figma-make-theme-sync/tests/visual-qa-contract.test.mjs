import assert from 'node:assert/strict';
import { spawnSync } from 'node:child_process';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import test from 'node:test';
import { fileURLToPath } from 'node:url';

import {
  createRunManifest,
  getBaselineForIteration,
  getRunRoot,
  loadRunManifest,
  recordBaselineSnapshot,
  recordResumeEvent,
  writeRunManifest,
} from '../scripts/visual-qa-common.mjs';
import { runVisualQaReport } from '../scripts/prepare-visual-qa-report.mjs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const themeRoot = path.resolve(__dirname, '../../../..');
const figmaCaptureScript = path.join(themeRoot, '.agents/skills/figma-make-theme-sync/scripts/capture-figma-make-screenshots.mjs');
const wpCaptureScript = path.join(themeRoot, '.agents/skills/figma-make-theme-sync/scripts/capture-wp-screenshots.mjs');
const lighthouseAuditScript = path.join(themeRoot, '.agents/skills/figma-make-theme-sync/scripts/run-lighthouse-audit.mjs');
const resumeScript = path.join(themeRoot, '.agents/skills/figma-make-theme-sync/scripts/resume-visual-qa-run.mjs');
const reportScript = path.join(themeRoot, '.agents/skills/figma-make-theme-sync/scripts/prepare-visual-qa-report.mjs');

function runNode(scriptPath, args) {
  return spawnSync('node', [scriptPath, ...args], {
    cwd: themeRoot,
    encoding: 'utf8',
  });
}

function getCombinedOutput(result) {
  return `${result.stdout || ''}${result.stderr || ''}`;
}

function hasSandboxSpawnError(result) {
  return Boolean(result.error && /EPERM/.test(result.error.message));
}

test('getRunRoot is stable when run-id is explicit', () => {
  const artifactRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'figma-skill-runroot-'));
  const first = getRunRoot('front-page', 'shared-run', artifactRoot);
  const second = getRunRoot('front-page', 'shared-run', artifactRoot);

  assert.equal(first, second);
});

test('prepare-report requires --run-id', () => {
  const result = runNode(reportScript, ['--target-name', 'page']);

  assert.equal(result.status, 1);
  if (!hasSandboxSpawnError(result)) {
    assert.match(getCombinedOutput(result), /--run-id/);
  }
});

test('lighthouse audit requires --run-id', () => {
  const result = runNode(lighthouseAuditScript, ['--target-name', 'page']);

  assert.equal(result.status, 1);
  if (!hasSandboxSpawnError(result)) {
    assert.match(getCombinedOutput(result), /--run-id/);
  }
});

test('resume runner requires --run-id', () => {
  const result = runNode(resumeScript, ['--target-name', 'page']);

  assert.equal(result.status, 1);
  if (!hasSandboxSpawnError(result)) {
    assert.match(getCombinedOutput(result), /--run-id/);
  }
});

test('capture-wp rejects legacy --url alias', () => {
  const result = runNode(wpCaptureScript, [
    '--target-name',
    'page',
    '--run-id',
    'shared-run',
    '--url',
    'http://example.com',
  ]);

  assert.equal(result.status, 1);
  if (!hasSandboxSpawnError(result)) {
    assert.match(getCombinedOutput(result), /Use --preview-url instead/);
  }
});

test('capture-figma rejects legacy --figma-url alias', () => {
  const result = runNode(figmaCaptureScript, [
    '--target-name',
    'page',
    '--run-id',
    'shared-run',
    '--figma-url',
    'https://www.figma.com/make/example',
  ]);

  assert.equal(result.status, 1);
  if (!hasSandboxSpawnError(result)) {
    assert.match(getCombinedOutput(result), /Use --figma-capture-url instead/);
  }
});

test('prepare-report writes into the explicit run root', () => {
  const artifactRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'figma-skill-report-'));
  const result = runNode(reportScript, [
    '--target-name',
    'page',
    '--run-id',
    'contract-test',
    '--artifact-root',
    artifactRoot,
    '--iteration',
    '1',
  ]);
  const expectedJson = path.join(artifactRoot, 'page', 'contract-test', 'reports', 'iter-1.json');

  assert.equal(result.status, 0);
  assert.ok(fs.existsSync(expectedJson));
});

test('prepare-report includes lighthouse summary when present', () => {
  const artifactRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'figma-skill-performance-'));
  const runRoot = path.join(artifactRoot, 'page', 'contract-test');
  const performanceDir = path.join(runRoot, 'performance', 'iter-1');
  const summaryPath = path.join(performanceDir, 'summary.json');

  fs.mkdirSync(performanceDir, { recursive: true });
  fs.writeFileSync(summaryPath, JSON.stringify({
    targetName: 'page',
    runId: 'contract-test',
    iteration: 1,
    url: 'http://example.com',
    runRoot,
    status: 'warning',
    thresholds: {
      performance_score: 0.75,
      lcp_ms: 2500,
      cls: 0.1,
      inp_ms: 200,
    },
    mobile: {
      score: 0.62,
      web_vitals: {
        lcp: 3200,
        cls: 0.04,
        inp: 180,
      },
    },
    desktop: {
      score: 0.91,
      web_vitals: {
        lcp: 1400,
        cls: 0.01,
        inp: 110,
      },
    },
    applied_fixes: ['preload-fonts'],
    remaining_failures: [{ metric: 'lcp_ms', actual: 3200, threshold: 2500 }],
  }, null, 2));

  const result = runNode(reportScript, [
    '--target-name',
    'page',
    '--run-id',
    'contract-test',
    '--artifact-root',
    artifactRoot,
    '--iteration',
    '1',
  ]);
  const reportJson = path.join(runRoot, 'reports', 'iter-1.json');
  const reportMarkdown = path.join(runRoot, 'reports', 'iter-1.md');
  const report = JSON.parse(fs.readFileSync(reportJson, 'utf8'));

  assert.equal(result.status, 0);
  assert.equal(report.performance_audit.status, 'warning');
  assert.match(fs.readFileSync(reportMarkdown, 'utf8'), /Performance Audit/);
});

test('loadRunManifest infers current iteration from existing iter directories', () => {
  const artifactRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'figma-skill-manifest-'));
  const runRoot = path.join(artifactRoot, 'page', 'contract-test');

  fs.mkdirSync(path.join(runRoot, 'output', 'iter-4'), { recursive: true });

  const manifest = loadRunManifest(runRoot, { targetName: 'page', runId: 'contract-test' });

  assert.equal(manifest.current_iteration, 4);
});

test('runVisualQaReport supports iter-4 and includes resume context', async () => {
  const artifactRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'figma-skill-iter4-'));
  const runRoot = path.join(artifactRoot, 'page', 'contract-test');
  const reportDir = path.join(runRoot, 'reports');
  const outputDir = path.join(runRoot, 'output', 'iter-4');
  const performanceDir = path.join(runRoot, 'performance', 'iter-4');
  const inputDir = path.join(runRoot, 'input');
  const manifest = createRunManifest({
    targetName: 'page',
    runId: 'contract-test',
    runRoot,
  });

  fs.mkdirSync(reportDir, { recursive: true });
  fs.mkdirSync(outputDir, { recursive: true });
  fs.mkdirSync(performanceDir, { recursive: true });
  fs.mkdirSync(inputDir, { recursive: true });
  fs.writeFileSync(path.join(inputDir, 'manifest.json'), JSON.stringify({ status: 'completed' }, null, 2));
  fs.writeFileSync(path.join(outputDir, 'manifest.json'), JSON.stringify({ status: 'completed' }, null, 2));
  fs.writeFileSync(path.join(performanceDir, 'summary.json'), JSON.stringify({
    targetName: 'page',
    runId: 'contract-test',
    iteration: 4,
    url: 'http://example.com',
    runRoot,
    status: 'pass',
    thresholds: {
      performance_score: 0.75,
      lcp_ms: 2500,
      cls: 0.1,
      inp_ms: 200,
    },
    mobile: { score: 0.9, web_vitals: { lcp: 2000, cls: 0.02, inp: 100 } },
    desktop: { score: 0.95, web_vitals: { lcp: 1200, cls: 0.01, inp: 80 } },
    applied_fixes: [],
    remaining_failures: [],
  }, null, 2));

  recordBaselineSnapshot(manifest, {
    mode: 'initial',
    refreshedBeforeIteration: 1,
    pageKey: 'home',
    figmaUrl: 'https://www.figma.com/make/example',
    figmaCaptureCompleted: true,
    designContextRefreshed: false,
  });
  recordBaselineSnapshot(manifest, {
    mode: 'refresh',
    refreshedBeforeIteration: 4,
    pageKey: 'home',
    figmaUrl: 'https://www.figma.com/make/example?screen=refresh',
    figmaCaptureCompleted: true,
    designContextRefreshed: true,
    note: 'refresh baseline',
  });
  recordResumeEvent(manifest, {
    iteration: 4,
    baselineMode: 'refresh',
    baselineGeneration: 2,
    designContextRefreshRequired: true,
    note: 'new round',
  });
  manifest.current_iteration = 4;
  writeRunManifest(manifest);

  const report = await runVisualQaReport({
    targetName: 'page',
    runId: 'contract-test',
    artifactRoot,
    iteration: 4,
    breakpoints: ['sm'],
    finalStatus: 'pending_review',
  });
  const reportJson = JSON.parse(fs.readFileSync(report.jsonPath, 'utf8'));
  const reportMarkdown = fs.readFileSync(report.markdownPath, 'utf8');

  assert.equal(reportJson.iteration, 4);
  assert.equal(reportJson.baseline.generation, 2);
  assert.equal(reportJson.resume_event.baseline_mode, 'refresh');
  assert.equal(getBaselineForIteration(manifest, 4).generation, 2);
  assert.match(reportMarkdown, /Run Context/);
});
