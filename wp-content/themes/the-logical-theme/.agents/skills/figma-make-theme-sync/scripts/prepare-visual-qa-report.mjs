#!/usr/bin/env node

import crypto from 'node:crypto';
import fs from 'node:fs';
import path from 'node:path';
import { pathToFileURL } from 'node:url';
import {
  BREAKPOINTS,
  ensureDir,
  fileExists,
  getBaselineForIteration,
  getBreakpointList,
  getRunRoot,
  getResumeEventForIteration,
  loadRunManifest,
  parseArgs,
  parseIterationValue,
  readCanonicalArg,
  readJsonFile,
  requireCanonicalArg,
  requireRunId,
  writeJson,
  writeText,
} from './visual-qa-common.mjs';

function readPngMetadata(filePath) {
  if (!fileExists(filePath)) {
    return null;
  }

  const buffer = fs.readFileSync(filePath);

  if (buffer.length < 24 || buffer.toString('ascii', 12, 16) !== 'IHDR') {
    return {
      byte_size: buffer.length,
      sha256: crypto.createHash('sha256').update(buffer).digest('hex'),
      format: 'unknown',
    };
  }

  return {
    byte_size: buffer.length,
    sha256: crypto.createHash('sha256').update(buffer).digest('hex'),
    format: 'png',
    width: buffer.readUInt32BE(16),
    height: buffer.readUInt32BE(20),
  };
}

function buildObjectiveMetrics(inputPath, outputPath, viewport) {
  const input = readPngMetadata(inputPath);
  const output = readPngMetadata(outputPath);

  return {
    input_exists: Boolean(input),
    output_exists: Boolean(output),
    viewport,
    input,
    output,
    identical_binary: Boolean(input && output && input.sha256 === output.sha256),
    byte_delta: input && output ? output.byte_size - input.byte_size : null,
  };
}

function buildMarkdown(report) {
  const lines = [
    `# Visual QA Iteration ${report.iteration}`,
    '',
    `- Target: \`${report.targetName}\``,
    `- Status: \`${report.status}\``,
    `- Run root: \`${report.runRoot}\``,
    '',
    '## Breakpoints',
    '',
  ];

  for (const entry of report.entries) {
    lines.push(`### ${entry.breakpoint}`);
    lines.push('');
    lines.push(`- Input: \`${entry.input}\``);
    lines.push(`- Output: \`${entry.output}\``);
    lines.push(`- Review status: \`${entry.review_status}\``);
    lines.push(`- Binary identical: \`${entry.objective_metrics.identical_binary}\``);
    lines.push(`- Byte delta: \`${entry.objective_metrics.byte_delta}\``);
    if (entry.capture_manifest_status) {
      lines.push(`- Capture manifest status: \`${entry.capture_manifest_status}\``);
    }
    lines.push(`- Notes: ${entry.notes.length > 0 ? entry.notes.join('; ') : 'pending manual/LLM review'}`);
    lines.push('');
  }

  lines.push('## Objective Metrics');
  lines.push('');
  lines.push('- Each entry includes viewport, file presence, PNG dimensions, SHA-256 hashes, and byte deltas.');
  lines.push('- Use these metrics to detect missing captures or obviously inconsistent output before semantic review.');
  lines.push('');

  lines.push('## Review Checklist');
  lines.push('');
  lines.push('- layout_spacing');
  lines.push('- content_hierarchy');
  lines.push('- typography_scale');
  lines.push('- media_crop_or_size');
  lines.push('- cta_navigation_placement');
  lines.push('');

  return `${lines.join('\n')}\n`;
}

function buildFinalSummary(targetName, runRoot, finalStatus, iteration) {
  return [
    '# Visual QA Final Summary',
    '',
    `- Target: \`${targetName}\``,
    `- Final status: \`${finalStatus}\``,
    `- Last iteration: \`${iteration}\``,
    `- Run root: \`${runRoot}\``,
    '',
    'Use the latest `reports/iter-N.json` objective metrics first, then complete the semantic screenshot review and document residual mismatches.',
    '',
  ].join('\n');
}

function readManifestStatus(filePath) {
  if (!fileExists(filePath)) {
    return null;
  }

  try {
    const decoded = JSON.parse(fs.readFileSync(filePath, 'utf8'));
    return typeof decoded.status === 'string' ? decoded.status : null;
  } catch {
    return null;
  }
}

function buildPerformanceMarkdownSection(summary) {
  if (!summary) {
    return [];
  }

  return [
    '## Performance Audit',
    '',
    `- Status: \`${summary.status}\``,
    `- URL: \`${summary.url}\``,
    `- Mobile score: \`${summary.mobile?.score ?? null}\``,
    `- Desktop score: \`${summary.desktop?.score ?? null}\``,
    `- Mobile LCP: \`${summary.mobile?.web_vitals?.lcp ?? null}\` ms`,
    `- Mobile CLS: \`${summary.mobile?.web_vitals?.cls ?? null}\``,
    `- Mobile INP: \`${summary.mobile?.web_vitals?.inp ?? null}\` ms`,
    `- Applied fixes: ${Array.isArray(summary.applied_fixes) && summary.applied_fixes.length > 0 ? summary.applied_fixes.join('; ') : 'none recorded'}`,
    `- Remaining failures: ${Array.isArray(summary.remaining_failures) && summary.remaining_failures.length > 0 ? summary.remaining_failures.map((failure) => failure.metric).join('; ') : 'none'}`,
    '- Note: Lighthouse metrics are lab data, not field data or CrUX.',
    '',
  ];
}

function buildRunContextMarkdownSection(report) {
  const baseline = report.baseline;
  const resumeEvent = report.resume_event;

  if (!baseline && !resumeEvent) {
    return [];
  }

  return [
    '## Run Context',
    '',
    `- Baseline generation: \`${baseline?.generation ?? null}\``,
    `- Baseline mode: \`${baseline?.mode ?? 'initial'}\``,
    `- Baseline refreshed before iteration: \`${baseline?.refreshed_before_iteration ?? 1}\``,
    `- Design context refreshed: \`${baseline?.design_context_refreshed ?? false}\``,
    `- Resume event: \`${resumeEvent ? 'yes' : 'no'}\``,
    `- Resume baseline mode: \`${resumeEvent?.baseline_mode ?? 'n/a'}\``,
    `- Resume note: ${resumeEvent?.note ?? 'none'}`,
    '',
  ];
}

export async function runVisualQaReport({
  targetName,
  runId,
  artifactRoot,
  iteration = 1,
  breakpoints,
  finalStatus = 'pending_review',
}) {
  const normalizedIteration = parseIterationValue(iteration, 1);
  const runRoot = getRunRoot(targetName, runId, artifactRoot);
  const runManifest = loadRunManifest(runRoot, { targetName, runId });
  const baseline = getBaselineForIteration(runManifest, normalizedIteration);
  const resumeEvent = getResumeEventForIteration(runManifest, normalizedIteration);
  const reportDir = ensureDir(path.join(runRoot, 'reports'));
  const diffDir = ensureDir(path.join(runRoot, 'diff', `iter-${normalizedIteration}`));
  const performanceSummaryPath = path.join(runRoot, 'performance', `iter-${normalizedIteration}`, 'summary.json');
  const performanceSummary = readJsonFile(performanceSummaryPath);
  const entries = breakpoints.map((breakpoint) => {
    const inputPath = path.join(runRoot, 'input', `${breakpoint}.png`);
    const outputPath = path.join(runRoot, 'output', `iter-${normalizedIteration}`, `${breakpoint}.png`);
    const inputManifestPath = path.join(runRoot, 'input', 'manifest.json');
    const outputManifestPath = path.join(runRoot, 'output', `iter-${normalizedIteration}`, 'manifest.json');
    const missing = [];

    if (!fileExists(inputPath)) {
      missing.push('missing input screenshot');
    }

    if (!fileExists(outputPath)) {
      missing.push('missing output screenshot');
    }

    return {
      breakpoint,
      viewport: BREAKPOINTS[breakpoint],
      input: inputPath,
      output: outputPath,
      diff_dir: diffDir,
      review_status: missing.length > 0 ? 'blocked' : 'pending',
      capture_manifest_status: readManifestStatus(outputManifestPath) || readManifestStatus(inputManifestPath),
      objective_metrics: buildObjectiveMetrics(inputPath, outputPath, BREAKPOINTS[breakpoint]),
      checklist: [
        'layout_spacing',
        'content_hierarchy',
        'typography_scale',
        'media_crop_or_size',
        'cta_navigation_placement',
      ],
      notes: missing,
    };
  });

  const report = {
    targetName,
    runRoot,
    iteration: normalizedIteration,
    status: entries.some((entry) => entry.review_status === 'blocked') ? 'blocked' : 'pending_review',
    finalStatus,
    compare_mode: 'llm_screenshot_review_with_metrics',
    baseline,
    resume_event: resumeEvent,
    performance_audit: performanceSummary,
    entries,
  };

  const jsonPath = path.join(reportDir, `iter-${normalizedIteration}.json`);
  const markdownPath = path.join(reportDir, `iter-${normalizedIteration}.md`);
  const runContextSection = buildRunContextMarkdownSection(report).join('\n');

  writeJson(jsonPath, report);
  writeText(markdownPath, `${buildMarkdown(report)}${runContextSection}${buildPerformanceMarkdownSection(performanceSummary).join('\n')}`);
  writeText(
    path.join(reportDir, 'final-summary.md'),
    `${buildFinalSummary(targetName, runRoot, finalStatus, normalizedIteration)}${runContextSection}${buildPerformanceMarkdownSection(performanceSummary).join('\n')}`,
  );

  return {
    jsonPath,
    markdownPath,
    runRoot,
    iteration: normalizedIteration,
  };
}

async function main() {
  const args = parseArgs(process.argv);
  const targetName = requireCanonicalArg(args, 'target-name', ['targetName', 'target_name']);
  const runId = requireRunId(args);
  const artifactRoot = readCanonicalArg(args, 'artifact-root', ['artifactRoot', 'artifact_root']);
  const iteration = parseIterationValue(readCanonicalArg(args, 'iteration'), 1);
  const breakpoints = getBreakpointList(readCanonicalArg(args, 'breakpoints'));
  const finalStatus = readCanonicalArg(args, 'final-status', ['finalStatus', 'final_status']) || 'pending_review';
  const result = await runVisualQaReport({
    targetName,
    runId,
    artifactRoot,
    iteration,
    breakpoints,
    finalStatus,
  });

  process.stdout.write(`${result.jsonPath}\n`);
}

if (process.argv[1] && import.meta.url === pathToFileURL(process.argv[1]).href) {
  main().catch((error) => {
    process.stderr.write(`${error.message}\n`);
    process.exit(1);
  });
}
