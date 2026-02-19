import fs from 'node:fs';
import path from 'node:path';

const themeDir = process.cwd();
const manifestPath = path.join(themeDir, 'config', 'hyperui-manifest.json');
const outputPath = path.join(themeDir, 'config', 'used-blocks.json');

function ensureDir(dirPath) {
  fs.mkdirSync(dirPath, { recursive: true });
}

function readJson(filePath) {
  if (!fs.existsSync(filePath)) {
    return null;
  }
  return JSON.parse(fs.readFileSync(filePath, 'utf8'));
}

function extractClasses(html) {
  const classes = new Set();
  const classRegex = /class\s*=\s*"([^"]+)"/g;
  let match = classRegex.exec(html);
  while (match) {
    const tokens = match[1].split(/\s+/).map((token) => token.trim()).filter(Boolean);
    tokens.forEach((token) => {
      classes.add(token);
      classes.add(mapColorClassToken(token));
    });
    match = classRegex.exec(html);
  }
  return classes;
}

function mapColorClassToken(token) {
  if (!token) return token;

  const parts = token.split(':');
  const baseRaw = parts.pop();
  const prefix = parts.join(':');

  let base = baseRaw;
  let bang = '';
  if (base.startsWith('!')) {
    bang = '!';
    base = base.slice(1);
  }

  const supported = new Set([
    'bg', 'text', 'border', 'ring', 'from', 'to', 'via',
    'decoration', 'placeholder', 'fill', 'stroke', 'outline',
    'divide', 'shadow', 'caret', 'accent'
  ]);

  let mappedBase = base;
  const scaleMatch = base.match(/^(?<utility>[a-z-]+)-(?<color>[a-z]+)-(?<shade>\d{2,3})(?<opacity>\/\d+)?$/);
  if (scaleMatch?.groups) {
    const { utility, color, shade, opacity = '' } = scaleMatch.groups;
    if (supported.has(utility)) {
      mappedBase = `${utility}-${pickTargetColor(color, Number.parseInt(shade, 10))}${opacity}`;
    }
  } else {
    const hexMatch = base.match(/^(?<utility>[a-z-]+)-\[#(?:[0-9a-fA-F]{3,8})\](?<opacity>\/\d+)?$/);
    if (hexMatch?.groups) {
      const { utility, opacity = '' } = hexMatch.groups;
      if (supported.has(utility)) {
        mappedBase = `${utility}-primary${opacity}`;
      }
    }
  }

  const outBase = `${bang}${mappedBase}`;
  return prefix ? `${prefix}:${outBase}` : outBase;
}

function pickTargetColor(color, shade) {
  return shade >= 500 ? 'secondary' : 'primary';
}

function extractBody(html) {
  const match = html.match(/<body[^>]*>([\s\S]*?)<\/body>/i);
  return match ? match[1] : html;
}

function buildSafelistFromManifest(manifest) {
  const classes = new Set();
  const collections = manifest?.collections && typeof manifest.collections === 'object'
    ? manifest.collections
    : {};

  for (const collectionName of Object.keys(collections)) {
    const slugs = collections[collectionName];
    if (!slugs || typeof slugs !== 'object') continue;

    for (const slugName of Object.keys(slugs)) {
      const entry = slugs[slugName];
      const variants = entry?.variants && typeof entry.variants === 'object' ? entry.variants : {};

      for (const variant of Object.keys(variants)) {
        const htmlPath = variants[variant]?.path;
        if (!htmlPath) continue;

        const fullPath = path.join(themeDir, htmlPath);
        if (!fs.existsSync(fullPath)) continue;

        const html = fs.readFileSync(fullPath, 'utf8');
        const body = extractBody(html);
        extractClasses(body).forEach((token) => classes.add(token));
      }
    }
  }

  return Array.from(classes).sort((a, b) => a.localeCompare(b));
}

const manifest = readJson(manifestPath);
if (!manifest) {
  throw new Error(`Manifest not found at ${manifestPath}. Run hyperui:manifest first.`);
}

const safelist = buildSafelistFromManifest(manifest);
const payload = {
  version: 1,
  generatedAt: new Date().toISOString(),
  safelist
};

ensureDir(path.dirname(outputPath));
fs.writeFileSync(outputPath, `${JSON.stringify(payload, null, 2)}\n`, 'utf8');
console.log(`Used blocks written: ${outputPath}`);
