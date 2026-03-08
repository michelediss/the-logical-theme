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
const reportScript = path.join(themeRoot, '.agents/skills/figma-make-theme-sync/scripts/prepare-visual-qa-report.mjs');

function runNode(scriptPath, args) {
  return spawnSync('node', [scriptPath, ...args], {
    cwd: themeRoot,
    encoding: 'utf8',
  });
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
  assert.match(result.stderr, /--run-id/);
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
  assert.match(result.stderr, /Use --preview-url instead/);
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
  assert.match(result.stderr, /Use --figma-capture-url instead/);
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
