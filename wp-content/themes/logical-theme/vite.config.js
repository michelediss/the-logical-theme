import fs from 'node:fs';
import path from 'node:path';
import { defineConfig } from 'vite';

const ENTRY_ROOT = 'src/entries';
const OUTPUT_DIR = 'dist';

function walkEntryFiles(dirPath, acc) {
  if (!fs.existsSync(dirPath)) {
    return;
  }

  for (const entry of fs.readdirSync(dirPath, { withFileTypes: true })) {
    const absolutePath = path.join(dirPath, entry.name);
    if (entry.isDirectory()) {
      walkEntryFiles(absolutePath, acc);
      continue;
    }

    if (!entry.isFile()) {
      continue;
    }

    if (!/\.(js|ts)$/.test(entry.name)) {
      continue;
    }

    const key = path.basename(entry.name, path.extname(entry.name));
    if (acc[key]) {
      throw new Error(
        `Duplicate Vite entry key "${key}" found. Entry basenames in ${ENTRY_ROOT} must be unique.`
      );
    }

    acc[key] = absolutePath;
  }
}

function buildInputMap(themeRoot) {
  const entriesDir = path.join(themeRoot, ENTRY_ROOT);
  const input = {};
  walkEntryFiles(entriesDir, input);

  const editorPath = path.join(themeRoot, 'src', 'editor.js');
  if (fs.existsSync(editorPath)) {
    input.editor = editorPath;
  }

  return input;
}

function normalizeManifestEntryKeysPlugin(themeRoot) {
  return {
    name: 'logical-theme-normalize-manifest-entry-keys',
    closeBundle() {
      const manifestPath = path.join(themeRoot, OUTPUT_DIR, 'manifest.json');
      if (!fs.existsSync(manifestPath)) {
        return;
      }

      const raw = fs.readFileSync(manifestPath, 'utf8');
      const manifest = JSON.parse(raw);
      const keyMap = {};

      Object.entries(manifest).forEach(([key, value]) => {
        if (!value || typeof value !== 'object') {
          return;
        }

        if (value.isEntry && typeof value.src === 'string' && value.src !== '') {
          const normalized = path.basename(value.src, path.extname(value.src));
          keyMap[key] = normalized;
        }
      });

      const normalizedManifest = {};
      Object.entries(manifest).forEach(([key, value]) => {
        const nextKey = keyMap[key] || key;
        const nextValue = { ...value };

        if (Array.isArray(nextValue.imports)) {
          nextValue.imports = nextValue.imports.map((importKey) => keyMap[importKey] || importKey);
        }

        normalizedManifest[nextKey] = nextValue;
      });

      fs.writeFileSync(`${manifestPath}`, `${JSON.stringify(normalizedManifest, null, 2)}\n`, 'utf8');
    }
  };
}

export default defineConfig({
  plugins: [normalizeManifestEntryKeysPlugin(__dirname)],
  build: {
    outDir: OUTPUT_DIR,
    emptyOutDir: true,
    manifest: true,
    cssCodeSplit: true,
    rollupOptions: {
      input: buildInputMap(__dirname)
    }
  }
});
