import fs from "node:fs/promises";
import path from "node:path";

const SCRIPT_DIR = path.dirname(new URL(import.meta.url).pathname);
export const PLUGIN_ROOT = path.resolve(SCRIPT_DIR, "..", "..");
export const WORKSPACE_ROOT = path.resolve(PLUGIN_ROOT, "..", "..", "..");
export const THEME_ROOT = path.join(WORKSPACE_ROOT, "wp-content", "themes", "the-logical-theme");
export const AI_SOURCE_ROOT = path.join(WORKSPACE_ROOT, "wp-content", "uploads", "ai-source");
export const DEFAULT_FIGMA_CONFIG = path.join(THEME_ROOT, "figma.json");
export const INDEX_PATH = path.join(AI_SOURCE_ROOT, "index.json");

export function parseCliArgs(argv) {
  const args = {
    flags: new Set(),
    values: new Map(),
  };

  for (let index = 0; index < argv.length; index += 1) {
    const token = argv[index];
    if (!token.startsWith("--")) {
      throw new Error(`Unexpected argument: ${token}`);
    }

    const key = token.slice(2);
    const next = argv[index + 1];
    if (!next || next.startsWith("--")) {
      args.flags.add(key);
      continue;
    }

    args.values.set(key, next);
    index += 1;
  }

  return args;
}

export function getArg(args, key, fallback = undefined) {
  return args.values.has(key) ? args.values.get(key) : fallback;
}

export function hasFlag(args, key) {
  return args.flags.has(key);
}

export async function ensureDir(dirPath) {
  await fs.mkdir(dirPath, { recursive: true });
}

export async function fileExists(targetPath) {
  try {
    await fs.access(targetPath);
    return true;
  } catch {
    return false;
  }
}

export async function readJson(filePath) {
  const raw = await fs.readFile(filePath, "utf8");
  return JSON.parse(raw);
}

export async function writeJson(filePath, data) {
  await ensureDir(path.dirname(filePath));
  const serialized = `${JSON.stringify(data, null, 2)}\n`;
  await fs.writeFile(filePath, serialized, "utf8");
}

export function nowIso() {
  return new Date().toISOString();
}

export function toPageSlug(value) {
  return String(value)
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "") || "page";
}

export function buildPageRecord(pageConfig) {
  const pageId = pageConfig.id || pageConfig.page_id || pageConfig.slug || pageConfig.label;
  if (!pageId) {
    throw new Error("Page entry is missing a usable identifier.");
  }

  const pageSlug = toPageSlug(pageId);
  const pageTitle = pageConfig.label || pageConfig.title || pageId;

  return {
    pageId: String(pageId),
    pageSlug,
    pageTitle: String(pageTitle),
    figmaMcpUrl: pageConfig.figma_mcp_url || null,
    figmaScreenshotUrl: pageConfig.figma_screenshot_url || null,
    appUrl: pageConfig.app_url || null,
    siteUrl: pageConfig.site_url || null,
    wpTemplate: pageConfig.wp_template || null,
    enabled: pageConfig.enabled !== false,
    rawConfig: pageConfig,
  };
}

export async function loadFigmaConfig(configPath = DEFAULT_FIGMA_CONFIG) {
  const config = await readJson(configPath);
  const source = config?.figma;
  if (!source || !Array.isArray(source.pages)) {
    throw new Error(`Invalid figma config: expected figma.pages array in ${configPath}`);
  }

  const pages = source.pages.map((page) =>
    buildPageRecord({
      ...page,
      app_url: page.app_url || source.app_url || null,
    }),
  );

  return {
    configPath,
    appUrl: source.app_url || null,
    source: source.source || null,
    pages,
  };
}

export function selectPages(figmaConfig, options = {}) {
  const enabledOnly = options.enabledOnly !== false;
  const allRequested = options.all === true;
  const pageId = options.pageId || null;

  let pages = figmaConfig.pages;
  if (enabledOnly) {
    pages = pages.filter((page) => page.enabled);
  }

  if (allRequested) {
    return pages;
  }

  if (pageId) {
    const match = pages.find((page) => page.pageId === pageId || page.pageSlug === pageId);
    if (!match) {
      throw new Error(`Page '${pageId}' not found in ${figmaConfig.configPath}`);
    }
    return [match];
  }

  throw new Error("Select a page with --page-id <id> or use --all.");
}

export function getPageRoot(pageSlug) {
  return path.join(AI_SOURCE_ROOT, pageSlug);
}

export function getPagePaths(pageSlug) {
  const root = getPageRoot(pageSlug);
  return {
    root,
    manifest: path.join(root, "manifest.json"),
    code: path.join(root, "code"),
    figmaRawCode: path.join(root, "code", "figma-raw"),
    wpDraftCode: path.join(root, "code", "wp-draft"),
    wpReviewedCode: path.join(root, "code", "wp-reviewed"),
    wpOptimizedCode: path.join(root, "code", "wp-optimized"),
    screenFigma: path.join(root, "screen-figma"),
    screenWp: path.join(root, "screen-wp"),
    lighthouse: path.join(root, "lighthouse"),
    reports: path.join(root, "reports"),
  };
}

export function buildDefaultManifest(page) {
  const pagePaths = getPagePaths(page.pageSlug);

  return {
    page_slug: page.pageSlug,
    page_title: page.pageTitle,
    status: {
      manifest_initialized: true,
      figma_ingested: false,
      figma_screenshots_ready: false,
      wp_generated: false,
      wp_reviewed: false,
      review_auto_pass_used: false,
      lighthouse_ready: false,
      wp_optimized: false,
    },
    paths: {
      figma_raw_code_dir: path.relative(WORKSPACE_ROOT, pagePaths.figmaRawCode),
      wp_draft_code_dir: path.relative(WORKSPACE_ROOT, pagePaths.wpDraftCode),
      wp_reviewed_code_dir: path.relative(WORKSPACE_ROOT, pagePaths.wpReviewedCode),
      wp_optimized_code_dir: path.relative(WORKSPACE_ROOT, pagePaths.wpOptimizedCode),
      figma_screens_dir: path.relative(WORKSPACE_ROOT, pagePaths.screenFigma),
      wp_screens_dir: path.relative(WORKSPACE_ROOT, pagePaths.screenWp),
      lighthouse_dir: path.relative(WORKSPACE_ROOT, pagePaths.lighthouse),
      reports_dir: path.relative(WORKSPACE_ROOT, pagePaths.reports),
    },
    source: {
      figma_entry_id: page.pageId,
      figma_mcp_url: page.figmaMcpUrl,
      figma_screenshot_url: page.figmaScreenshotUrl,
      figma_input_version: null,
    },
    local_url: page.siteUrl,
    template_hint: page.wpTemplate,
    current_stage: "initialized",
    current_code_variant: null,
    errors: [],
    warnings: [],
    history: [],
    last_updated_at: nowIso(),
  };
}

export function normalizeManifest(pageSlug, manifest) {
  const pagePaths = getPagePaths(pageSlug);

  return {
    ...manifest,
    page_slug: pageSlug,
    paths: {
      ...(manifest.paths || {}),
      figma_raw_code_dir: path.relative(WORKSPACE_ROOT, pagePaths.figmaRawCode),
      wp_draft_code_dir: path.relative(WORKSPACE_ROOT, pagePaths.wpDraftCode),
      wp_reviewed_code_dir: path.relative(WORKSPACE_ROOT, pagePaths.wpReviewedCode),
      wp_optimized_code_dir: path.relative(WORKSPACE_ROOT, pagePaths.wpOptimizedCode),
      figma_screens_dir: path.relative(WORKSPACE_ROOT, pagePaths.screenFigma),
      wp_screens_dir: path.relative(WORKSPACE_ROOT, pagePaths.screenWp),
      lighthouse_dir: path.relative(WORKSPACE_ROOT, pagePaths.lighthouse),
      reports_dir: path.relative(WORKSPACE_ROOT, pagePaths.reports),
    },
    source: {
      figma_entry_id: manifest.source?.figma_entry_id || null,
      figma_mcp_url: manifest.source?.figma_mcp_url || null,
      figma_screenshot_url: manifest.source?.figma_screenshot_url || null,
      figma_input_version: manifest.source?.figma_input_version || null,
    },
  };
}

export async function ensurePageStructure(page) {
  const pagePaths = getPagePaths(page.pageSlug);
  await Promise.all([
    ensureDir(AI_SOURCE_ROOT),
    ensureDir(pagePaths.root),
    ensureDir(pagePaths.figmaRawCode),
    ensureDir(pagePaths.wpDraftCode),
    ensureDir(pagePaths.wpReviewedCode),
    ensureDir(pagePaths.wpOptimizedCode),
    ensureDir(pagePaths.screenFigma),
    ensureDir(pagePaths.screenWp),
    ensureDir(pagePaths.lighthouse),
    ensureDir(pagePaths.reports),
  ]);

  if (!(await fileExists(pagePaths.manifest))) {
    await writeJson(pagePaths.manifest, buildDefaultManifest(page));
  } else {
    const manifest = await readJson(pagePaths.manifest);
    await writeJson(pagePaths.manifest, normalizeManifest(page.pageSlug, manifest));
  }

  return pagePaths;
}

export async function loadManifest(pageSlug) {
  const manifest = await readJson(getPagePaths(pageSlug).manifest);
  return normalizeManifest(pageSlug, manifest);
}

export async function saveManifest(pageSlug, manifest) {
  manifest.last_updated_at = nowIso();
  await writeJson(getPagePaths(pageSlug).manifest, manifest);
}

export async function appendHistory(pageSlug, entry) {
  const manifest = await loadManifest(pageSlug);
  manifest.history = Array.isArray(manifest.history) ? manifest.history : [];
  manifest.history.push({
    ...entry,
    recorded_at: nowIso(),
  });
  await saveManifest(pageSlug, manifest);
}

export async function ensureIndex() {
  if (!(await fileExists(INDEX_PATH))) {
    await writeJson(INDEX_PATH, {
      project: {
        name: "the-logical-theme",
        ai_source_version: 1,
      },
      pages: [],
    });
  }

  const index = await readJson(INDEX_PATH);
  const pages = Array.isArray(index.pages) ? index.pages : [];
  index.pages = pages.map((page) => ({
    ...page,
    manifest_path: path.relative(WORKSPACE_ROOT, getPagePaths(page.page_slug).manifest),
  }));
  return index;
}

export async function upsertIndexPage(page, stage) {
  const index = await ensureIndex();
  const pages = Array.isArray(index.pages) ? index.pages : [];
  const nextRecord = {
    page_slug: page.pageSlug,
    page_title: page.pageTitle,
    manifest_path: path.relative(WORKSPACE_ROOT, getPagePaths(page.pageSlug).manifest),
    current_stage: stage,
    last_updated_at: nowIso(),
  };

  const existingIndex = pages.findIndex((entry) => entry.page_slug === page.pageSlug);
  if (existingIndex === -1) {
    pages.push(nextRecord);
  } else {
    pages[existingIndex] = {
      ...pages[existingIndex],
      ...nextRecord,
    };
  }

  index.pages = pages.sort((left, right) => left.page_slug.localeCompare(right.page_slug));
  await writeJson(INDEX_PATH, index);
}

export async function copyPath(sourcePath, targetPath) {
  const stats = await fs.stat(sourcePath);
  if (stats.isDirectory()) {
    await ensureDir(targetPath);
    const entries = await fs.readdir(sourcePath);
    for (const entry of entries) {
      await copyPath(path.join(sourcePath, entry), path.join(targetPath, entry));
    }
    return;
  }

  await ensureDir(path.dirname(targetPath));
  await fs.copyFile(sourcePath, targetPath);
}

export async function cleanDir(targetDir) {
  await fs.rm(targetDir, { recursive: true, force: true });
  await ensureDir(targetDir);
}
