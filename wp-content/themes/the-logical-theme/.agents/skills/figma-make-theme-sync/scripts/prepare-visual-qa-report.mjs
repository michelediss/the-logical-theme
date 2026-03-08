#!/usr/bin/env node

import path from 'node:path';
import {
  BREAKPOINTS,
  ensureDir,
  fileExists,
  getBreakpointList,
  getRunRoot,
  parseArgs,
  writeJson,
  writeText,
} from './visual-qa-common.mjs';

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
    lines.push(`- Notes: ${entry.notes.length > 0 ? entry.notes.join('; ') : 'pending manual/LLM review'}`);
    lines.push('');
  }

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
    'Open the latest `reports/iter-N.json` and `reports/iter-N.md` files to complete the LLM screenshot review and document any residual mismatches.',
    '',
  ].join('\n');
}

async function main() {
  const args = parseArgs(process.argv);
  const targetName = args['target-name'] || args.targetName;
  const runId = args['run-id'] || args.runId;
  const artifactRoot = args['artifact-root'] || args.artifactRoot;
  const iteration = Number(args.iteration || 1);
  const breakpoints = getBreakpointList(args.breakpoints);
  const finalStatus = args['final-status'] || args.finalStatus || 'pending_review';

  if (!targetName) {
    throw new Error('Missing required --target-name');
  }

  if (!Number.isInteger(iteration) || iteration < 1 || iteration > 3) {
    throw new Error('Iteration must be an integer between 1 and 3');
  }

  const runRoot = getRunRoot(targetName, runId, artifactRoot);
  const reportDir = ensureDir(path.join(runRoot, 'reports'));
  const diffDir = ensureDir(path.join(runRoot, 'diff', `iter-${iteration}`));
  const entries = breakpoints.map((breakpoint) => {
    const inputPath = path.join(runRoot, 'input', `${breakpoint}.png`);
    const outputPath = path.join(runRoot, 'output', `iter-${iteration}`, `${breakpoint}.png`);
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
    iteration,
    status: entries.some((entry) => entry.review_status === 'blocked') ? 'blocked' : 'pending_review',
    finalStatus,
    entries,
  };

  const jsonPath = path.join(reportDir, `iter-${iteration}.json`);
  const markdownPath = path.join(reportDir, `iter-${iteration}.md`);

  writeJson(jsonPath, report);
  writeText(markdownPath, buildMarkdown(report));
  writeText(
    path.join(reportDir, 'final-summary.md'),
    buildFinalSummary(targetName, runRoot, finalStatus, iteration),
  );

  process.stdout.write(`${jsonPath}\n`);
}

main().catch((error) => {
  process.stderr.write(`${error.message}\n`);
  process.exit(1);
});
