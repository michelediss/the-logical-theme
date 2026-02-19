import fs from 'node:fs';
import path from 'node:path';

const themeDir = process.cwd();
const themeJsonPath = path.join(themeDir, 'theme.json');
const tokensCssPath = path.join(themeDir, 'src', 'generated', 'tokens.css');
const tailwindTokensPath = path.join(themeDir, 'src', 'generated', 'tailwind.tokens.json');

const DEFAULT_BREAKPOINTS = {
  null: 0,
  sm: '576px',
  md: '768px',
  lg: '1024px',
  xl: '1280px',
  '2xl': '1600px',
  '3xl': '1920px',
  '4xl': '2560px',
  '5xl': '3840px'
};

const DEFAULT_CONTAINERS = {
  sm: '540px',
  md: '720px',
  lg: '960px',
  xl: '1140px',
  '2xl': '1440px',
  '3xl': '1680px',
  '4xl': '1920px',
  '5xl': '2560px'
};

function readJson(filePath) {
  if (!fs.existsSync(filePath)) {
    throw new Error(`File not found: ${filePath}`);
  }

  const raw = fs.readFileSync(filePath, 'utf8');
  return JSON.parse(raw);
}

function ensureDir(filePath) {
  fs.mkdirSync(path.dirname(filePath), { recursive: true });
}

function normalizeHex(hex) {
  if (typeof hex !== 'string') return null;
  const value = hex.trim();
  if (!value.startsWith('#')) return null;

  if (value.length === 4) {
    const r = value[1];
    const g = value[2];
    const b = value[3];
    return `#${r}${r}${g}${g}${b}${b}`.toLowerCase();
  }

  if (value.length === 7) {
    return value.toLowerCase();
  }

  return null;
}

function hexToRgbChannels(hex) {
  const normalized = normalizeHex(hex);
  if (!normalized) return null;

  const r = Number.parseInt(normalized.slice(1, 3), 16);
  const g = Number.parseInt(normalized.slice(3, 5), 16);
  const b = Number.parseInt(normalized.slice(5, 7), 16);
  return `${r} ${g} ${b}`;
}

function sanitizeTokenKey(rawKey) {
  return String(rawKey || '')
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9-]/g, '-');
}

function extractColorTokens(themeJson) {
  const palette = Array.isArray(themeJson?.settings?.color?.palette) ? themeJson.settings.color.palette : [];
  const deduped = new Map();

  palette.forEach((entry) => {
    const slug = sanitizeTokenKey(entry?.slug);
    const hex = normalizeHex(entry?.color);
    const rgb = hexToRgbChannels(entry?.color);
    if (!slug || !hex || !rgb) return;
    deduped.set(slug, { key: slug, hex, rgb });
  });

  if (!deduped.has('black')) {
    const rgb = hexToRgbChannels('#000000');
    deduped.set('black', { key: 'black', hex: '#000000', rgb });
  }
  if (!deduped.has('white')) {
    const rgb = hexToRgbChannels('#ffffff');
    deduped.set('white', { key: 'white', hex: '#ffffff', rgb });
  }

  return Array.from(deduped.values());
}

function extractScreens(themeJson) {
  const fromCustom = themeJson?.settings?.custom?.lds?.breakpoints;
  const source = fromCustom && typeof fromCustom === 'object' ? fromCustom : DEFAULT_BREAKPOINTS;
  const screens = {};

  Object.entries(source).forEach(([key, value]) => {
    if (key === 'null') return;
    if (typeof value !== 'string' || value.trim() === '') return;
    screens[key] = value.trim();
  });

  return screens;
}

function extractContainer(themeJson) {
  const fromCustom = themeJson?.settings?.custom?.lds?.containerMaxWidths;
  const source = fromCustom && typeof fromCustom === 'object' ? fromCustom : DEFAULT_CONTAINERS;

  const screens = {};
  Object.entries(source).forEach(([key, value]) => {
    if (typeof value !== 'string' || value.trim() === '') return;
    screens[key] = value.trim();
  });

  return {
    center: true,
    padding: '1rem',
    screens
  };
}

function buildTailwindColors(colorTokens) {
  const colors = {};
  colorTokens.forEach((token) => {
    colors[token.key] = `rgb(var(--color-${token.key}-rgb) / <alpha-value>)`;
  });
  return colors;
}

function buildTokensCss(colorTokens) {
  const lines = [];
  lines.push('/* Auto-generated from theme.json. Do not edit manually. */');
  lines.push(':root {');

  colorTokens.forEach((token) => {
    lines.push(`  --color-${token.key}: ${token.hex};`);
    lines.push(`  --color-${token.key}-rgb: ${token.rgb};`);
  });

  lines.push('}');
  lines.push('');
  return lines.join('\n');
}

function main() {
  const themeJson = readJson(themeJsonPath);
  const colorTokens = extractColorTokens(themeJson);
  const screens = extractScreens(themeJson);
  const container = extractContainer(themeJson);
  const colors = buildTailwindColors(colorTokens);

  const tailwindTokens = {
    colors,
    screens,
    container
  };

  ensureDir(tokensCssPath);
  fs.writeFileSync(tokensCssPath, buildTokensCss(colorTokens), 'utf8');

  ensureDir(tailwindTokensPath);
  fs.writeFileSync(tailwindTokensPath, `${JSON.stringify(tailwindTokens, null, 2)}\n`, 'utf8');

  console.log(`Generated ${path.relative(themeDir, tokensCssPath)}`);
  console.log(`Generated ${path.relative(themeDir, tailwindTokensPath)}`);
}

main();
