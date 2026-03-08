import assert from 'node:assert/strict';
import { spawnSync } from 'node:child_process';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import test from 'node:test';
import { fileURLToPath } from 'node:url';

import { getRunRoot } from '../scripts/visual-qa-common.mjs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const themeRoot = path.resolve(__dirname, '../../../..');
const figmaCaptureScript = path.join(themeRoot, '.agents/skills/figma-make-theme-sync/scripts/capture-figma-make-screenshots.mjs');
const wpCaptureScript = path.join(themeRoot, '.agents/skills/figma-make-theme-sync/scripts/capture-wp-screenshots.mjs');
const lighthouseAuditScript = path.join(themeRoot, '.agents/skills/figma-make-theme-sync/scripts/run-lighthouse-audit.mjs');
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
