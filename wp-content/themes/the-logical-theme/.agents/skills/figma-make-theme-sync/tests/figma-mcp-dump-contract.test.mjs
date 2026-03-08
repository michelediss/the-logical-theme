import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const themeRoot = path.resolve(__dirname, '../../../../');
const dumpDir = path.join(themeRoot, '.artifacts', 'figma-mcp-debug', 'ugHr0Gh5yVbWe2mtIPCLIG');
const manifestPath = path.join(dumpDir, 'manifest.json');

test('figma MCP debug dump exists', () => {
  assert.equal(fs.existsSync(manifestPath), true, 'manifest.json should exist');
});

test('figma MCP debug dump manifest references existing files', () => {
  const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));

  assert.equal(typeof manifest.appUrl, 'string');
  assert.equal(Array.isArray(manifest.savedSources), true);
  assert.equal(Array.isArray(manifest.savedImages), true);
  assert.ok(manifest.savedSources.length > 0, 'savedSources should not be empty');
  assert.ok(manifest.savedImages.length > 0, 'savedImages should not be empty');

  for (const relPath of manifest.savedSources) {
    assert.equal(fs.existsSync(path.join(dumpDir, relPath)), true, `missing source file ${relPath}`);
  }

  for (const relPath of manifest.savedImages) {
    assert.equal(fs.existsSync(path.join(dumpDir, relPath)), true, `missing image file ${relPath}`);
  }
});
