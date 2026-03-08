#!/usr/bin/env node

import path from 'node:path';
import {
  ensureDir,
  getRunRoot,
  loadFigmaConfig,
  parseArgs,
  readCanonicalArg,
  requireCanonicalArg,
  requireRunId,
  resolveSiteCaptureUrl,
  writeJson,
  writeText,
} from './visual-qa-common.mjs';

const DEFAULT_THRESHOLDS = {
  performance_score: 0.75,
  lcp_ms: 2500,
  cls: 0.1,
  inp_ms: 200,
};

function getIteration(args) {
  const iteration = Number(readCanonicalArg(args, 'iteration') || 1);

  if (!Number.isInteger(iteration) || iteration < 1 || iteration > 3) {
    throw new Error('Iteration must be an integer between 1 and 3');
  }

  return iteration;
}

function normalizeNumber(value) {
  return Number.isFinite(value) ? value : null;
}

function readAuditValue(audits, id) {
  return normalizeNumber(audits?.[id]?.numericValue);
}

function evaluateProfile(lhr, thresholds) {
  const audits = lhr.audits ?? {};
  const performanceScore = normalizeNumber(lhr.categories?.performance?.score);
  const webVitals = {
    lcp: readAuditValue(audits, 'largest-contentful-paint'),
    cls: readAuditValue(audits, 'cumulative-layout-shift'),
    inp: readAuditValue(audits, 'interaction-to-next-paint'),
    fcp: readAuditValue(audits, 'first-contentful-paint'),
    tbt: readAuditValue(audits, 'total-blocking-time'),
    speed_index: readAuditValue(audits, 'speed-index'),
  };

  const opportunities = [
    'render-blocking-resources',
    'unused-css-rules',
    'unused-javascript',
    'offscreen-images',
    'modern-image-formats',
    'uses-optimized-images',
    'unminified-css',
    'unminified-javascript',
    'server-response-time',
  ]
    .map((id) => audits[id])
    .filter(Boolean)
    .map((audit) => ({
      id: audit.id,
      title: audit.title,
      score: normalizeNumber(audit.score),
      display_value: typeof audit.displayValue === 'string' ? audit.displayValue : null,
    }));

  const failures = [];

  if (performanceScore === null || performanceScore < thresholds.performance_score) {
    failures.push({
      metric: 'performance_score',
      actual: performanceScore,
      threshold: thresholds.performance_score,
    });
  }

  if (webVitals.lcp === null || webVitals.lcp > thresholds.lcp_ms) {
    failures.push({
      metric: 'lcp_ms',
      actual: webVitals.lcp,
      threshold: thresholds.lcp_ms,
    });
  }

  if (webVitals.cls === null || webVitals.cls > thresholds.cls) {
    failures.push({
      metric: 'cls',
      actual: webVitals.cls,
      threshold: thresholds.cls,
    });
  }

  if (webVitals.inp === null || webVitals.inp > thresholds.inp_ms) {
    failures.push({
      metric: 'inp_ms',
      actual: webVitals.inp,
      threshold: thresholds.inp_ms,
    });
  }

  return {
    status: failures.length === 0 ? 'pass' : 'warning',
    score: performanceScore,
    web_vitals: webVitals,
    opportunities: opportunities.slice(0, 5),
    failures,
  };
}

function buildMarkdown(summary) {
  const lines = [
    `# Lighthouse Performance Iteration ${summary.iteration}`,
    '',
    `- Target: \`${summary.targetName}\``,
    `- Status: \`${summary.status}\``,
    `- URL: \`${summary.url}\``,
    `- Run root: \`${summary.runRoot}\``,
    '',
    '## Mobile',
    '',
    `- Performance score: \`${summary.mobile.score}\``,
    `- LCP: \`${summary.mobile.web_vitals.lcp}\` ms`,
    `- CLS: \`${summary.mobile.web_vitals.cls}\``,
    `- INP: \`${summary.mobile.web_vitals.inp}\` ms`,
    '',
    '## Desktop',
    '',
    `- Performance score: \`${summary.desktop.score}\``,
    `- LCP: \`${summary.desktop.web_vitals.lcp}\` ms`,
    `- CLS: \`${summary.desktop.web_vitals.cls}\``,
    `- INP: \`${summary.desktop.web_vitals.inp}\` ms`,
    '',
    '## Thresholds',
    '',
    `- Mobile performance score >= \`${summary.thresholds.performance_score}\``,
    `- LCP <= \`${summary.thresholds.lcp_ms}\` ms`,
    `- CLS <= \`${summary.thresholds.cls}\``,
    `- INP <= \`${summary.thresholds.inp_ms}\` ms`,
    '',
    '## Notes',
    '',
    '- Lighthouse results are lab data, not field data or CrUX.',
    `- Applied fixes: ${summary.applied_fixes.length > 0 ? summary.applied_fixes.join('; ') : 'none recorded'}`,
    `- Remaining failures: ${summary.remaining_failures.length > 0 ? summary.remaining_failures.map((failure) => failure.metric).join('; ') : 'none'}`,
    '',
  ];

  return `${lines.join('\n')}\n`;
}

async function runProfile(lighthouse, chromeLauncher, url, outputDir, profile) {
  const chrome = await chromeLauncher.launch({
    chromeFlags: ['--headless=new', '--no-sandbox', '--disable-dev-shm-usage'],
  });

  try {
    const result = await lighthouse(url, {
      port: chrome.port,
      logLevel: 'error',
      output: ['json', 'html'],
      onlyCategories: ['performance'],
      preset: profile === 'desktop' ? 'desktop' : undefined,
    });

    if (!result?.lhr || !Array.isArray(result.report) || result.report.length < 2) {
      throw new Error(`Lighthouse returned an incomplete ${profile} report`);
    }

    const jsonPath = path.join(outputDir, `${profile}.json`);
    const htmlPath = path.join(outputDir, `${profile}.html`);

    writeText(jsonPath, result.report[0]);
    writeText(htmlPath, result.report[1]);

    return result.lhr;
  } finally {
    await chrome.kill();
  }
}

async function loadLighthouseDependencies() {
  try {
    const [{ default: lighthouse }, chromeLauncher] = await Promise.all([
      import('lighthouse'),
      import('chrome-launcher'),
    ]);

    return { lighthouse, chromeLauncher };
  } catch (error) {
    throw new Error(`Missing Lighthouse dependencies. Install them with npm before running this audit. ${error.message}`);
  }
}

async function main() {
  const args = parseArgs(process.argv);
  const config = loadFigmaConfig();
  const pageKey = readCanonicalArg(args, 'page-key', ['pageKey', 'page_key']);
  const previewUrl = readCanonicalArg(args, 'preview-url', ['url', 'previewUrl', 'preview_url']);
  const targetName = requireCanonicalArg(args, 'target-name', ['targetName', 'target_name']);
  const runId = requireRunId(args);
  const artifactRoot = readCanonicalArg(args, 'artifact-root', ['artifactRoot', 'artifact_root']);
  const iteration = getIteration(args);
  const appliedFixes = String(readCanonicalArg(args, 'applied-fixes', ['appliedFixes', 'applied_fixes']) || '')
    .split(',')
    .map((item) => item.trim())
    .filter(Boolean);
  const pageUrl = resolveSiteCaptureUrl({
    explicitUrl: previewUrl,
    pageKey,
    config,
  });
  const runRoot = getRunRoot(targetName, runId, artifactRoot);
  const outputDir = ensureDir(path.join(runRoot, 'performance', `iter-${iteration}`));
  const thresholds = { ...DEFAULT_THRESHOLDS };
  const { lighthouse, chromeLauncher } = await loadLighthouseDependencies();

  const [mobileLhr, desktopLhr] = await Promise.all([
    runProfile(lighthouse, chromeLauncher, pageUrl, outputDir, 'mobile'),
    runProfile(lighthouse, chromeLauncher, pageUrl, outputDir, 'desktop'),
  ]);

  const mobile = evaluateProfile(mobileLhr, thresholds);
  const desktop = evaluateProfile(desktopLhr, thresholds);
  const summary = {
    targetName,
    runId,
    iteration,
    url: pageUrl,
    runRoot,
    status: mobile.failures.length === 0 ? 'pass' : 'warning',
    thresholds,
    mobile,
    desktop,
    applied_fixes: appliedFixes,
    remaining_failures: mobile.failures,
  };

  const summaryPath = path.join(outputDir, 'summary.json');
  const markdownPath = path.join(runRoot, 'reports', `performance-iter-${iteration}.md`);

  writeJson(summaryPath, summary);
  writeText(markdownPath, buildMarkdown(summary));

  process.stdout.write(`${summaryPath}\n`);
}

main().catch((error) => {
  process.stderr.write(`${error.message}\n`);
  process.exit(1);
});
