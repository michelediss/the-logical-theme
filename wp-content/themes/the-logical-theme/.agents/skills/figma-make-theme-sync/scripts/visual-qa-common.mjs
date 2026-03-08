import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

export const BREAKPOINTS = {
  sm: { width: 640, height: 1600 },
  md: { width: 768, height: 1600 },
  lg: { width: 1024, height: 1600 },
  xl: { width: 1280, height: 1600 },
  '2xl': { width: 1536, height: 1600 },
};

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export function getThemeRoot() {
  return path.resolve(__dirname, '../../../../');
}

export function getArtifactsRoot() {
  return path.join(getThemeRoot(), '.artifacts', 'visual-qa');
}

export function parseArgs(argv) {
  const args = {};

  for (let index = 2; index < argv.length; index += 1) {
    const token = argv[index];

    if (!token.startsWith('--')) {
      continue;
    }

    const [rawKey, inlineValue] = token.slice(2).split('=', 2);
    const nextToken = argv[index + 1];

    if (inlineValue !== undefined) {
      args[rawKey] = inlineValue;
      continue;
    }

    if (!nextToken || nextToken.startsWith('--')) {
      args[rawKey] = 'true';
      continue;
    }

    args[rawKey] = nextToken;
    index += 1;
  }

  return args;
}

export function sanitizeSegment(value) {
  return String(value).trim().replace(/[^a-zA-Z0-9-_]+/g, '-').replace(/^-+|-+$/g, '') || 'unnamed';
}

export function getRunId(explicitRunId) {
  if (explicitRunId) {
    return sanitizeSegment(explicitRunId);
  }

  return new Date().toISOString().replace(/[:.]/g, '-');
}

export function ensureDir(dirPath) {
  fs.mkdirSync(dirPath, { recursive: true });
  return dirPath;
}

export function getBreakpointList(rawValue) {
  if (!rawValue) {
    return Object.keys(BREAKPOINTS);
  }

  const requested = String(rawValue)
    .split(',')
    .map((item) => item.trim())
    .filter(Boolean);

  const invalid = requested.filter((item) => !BREAKPOINTS[item]);

  if (invalid.length > 0) {
    throw new Error(`Unsupported breakpoints: ${invalid.join(', ')}`);
  }

  return requested;
}

export function getRunRoot(targetName, runId, explicitRoot) {
  const baseRoot = explicitRoot ? path.resolve(explicitRoot) : getArtifactsRoot();
  return path.join(baseRoot, sanitizeSegment(targetName), getRunId(runId));
}

export function writeJson(filePath, data) {
  ensureDir(path.dirname(filePath));
  fs.writeFileSync(filePath, JSON.stringify(data, null, 2) + '\n', 'utf8');
}

export function writeText(filePath, data) {
  ensureDir(path.dirname(filePath));
  fs.writeFileSync(filePath, data, 'utf8');
}

export function fileExists(filePath) {
  return fs.existsSync(filePath);
}

function isNonEmptyString(value) {
  return typeof value === 'string' && value.trim() !== '';
}

function isValidUrl(value) {
  if (!isNonEmptyString(value)) {
    return false;
  }

  try {
    const url = new URL(value);
    return url.protocol === 'http:' || url.protocol === 'https:';
  } catch {
    return false;
  }
}

export function getFigmaConfigPath() {
  return path.join(getThemeRoot(), 'figma.json');
}

export function loadFigmaConfig() {
  const configPath = getFigmaConfigPath();

  if (!fileExists(configPath)) {
    throw new Error(`Missing figma.json at ${configPath}`);
  }

  let data;

  try {
    data = JSON.parse(fs.readFileSync(configPath, 'utf8'));
  } catch (error) {
    throw new Error(`Invalid JSON in ${configPath}: ${error.message}`);
  }

  if (!data || typeof data !== 'object' || Array.isArray(data)) {
    throw new Error('figma.json must contain a top-level object');
  }

  const figma = data.figma;

  if (!figma || typeof figma !== 'object' || Array.isArray(figma)) {
    throw new Error("figma.json must contain a top-level 'figma' object");
  }

  if (figma.source !== 'make') {
    throw new Error("figma.source must be 'make'");
  }

  if (!isNonEmptyString(figma.app_url)) {
    throw new Error('figma.app_url must be a non-empty string');
  }

  if (!/^https:\/\/www\.figma\.com\/make\/[^/\s?#]+(?:[/?#].*)?$/.test(figma.app_url.trim())) {
    throw new Error('figma.app_url must match https://www.figma.com/make/APP_ID');
  }

  const pages = figma.pages;

  if (pages === undefined) {
    return data;
  }

  if (!pages || typeof pages !== 'object' || Array.isArray(pages)) {
    throw new Error('figma.pages must be an object when provided');
  }

  for (const [pageKey, pageValue] of Object.entries(pages)) {
    if (!pageValue || typeof pageValue !== 'object' || Array.isArray(pageValue)) {
      throw new Error(`figma.pages.${pageKey} must be an object`);
    }

    if (!isNonEmptyString(pageValue.figma_url)) {
      throw new Error(`figma.pages.${pageKey}.figma_url must be a non-empty string`);
    }

    if (!isNonEmptyString(pageValue.site_url)) {
      throw new Error(`figma.pages.${pageKey}.site_url must be a non-empty string`);
    }

    if (!isValidUrl(pageValue.figma_url)) {
      throw new Error(`figma.pages.${pageKey}.figma_url must be a valid HTTP/HTTPS URL`);
    }

    if (!isValidUrl(pageValue.site_url)) {
      throw new Error(`figma.pages.${pageKey}.site_url must be a valid HTTP/HTTPS URL`);
    }
  }

  return data;
}

export function resolveFigmaPageMapping(pageKey, config = loadFigmaConfig()) {
  if (!pageKey) {
    return null;
  }

  const mapping = config.figma?.pages?.[pageKey];

  if (!mapping) {
    throw new Error(`No figma.pages.${pageKey} mapping found in figma.json`);
  }

  return {
    pageKey,
    figmaUrl: mapping.figma_url.trim(),
    siteUrl: mapping.site_url.trim(),
    targetName: isNonEmptyString(mapping.target_name) ? mapping.target_name.trim() : null,
    label: isNonEmptyString(mapping.label) ? mapping.label.trim() : null,
  };
}

export function resolveFigmaCaptureUrl({ explicitUrl, pageKey, config }) {
  if (isNonEmptyString(explicitUrl)) {
    return explicitUrl.trim();
  }

  if (pageKey) {
    return resolveFigmaPageMapping(pageKey, config).figmaUrl;
  }

  return config.figma.app_url.trim();
}

export function resolveSiteCaptureUrl({ explicitUrl, pageKey, config }) {
  if (isNonEmptyString(explicitUrl)) {
    return explicitUrl.trim();
  }

  if (pageKey) {
    return resolveFigmaPageMapping(pageKey, config).siteUrl;
  }

  throw new Error('Missing site URL. Pass --url/preview_url or configure figma.pages.<page_key>.site_url');
}
