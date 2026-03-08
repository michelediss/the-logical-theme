#!/usr/bin/env node

import { BREAKPOINTS, getArtifactsRoot } from './visual-qa-common.mjs';

const payload = {
  source: 'visual-qa-common.mjs',
  compare_mode: 'llm_screenshot_review_with_metrics',
  artifacts_root: getArtifactsRoot(),
  breakpoints: BREAKPOINTS,
  max_iterations: 3,
};

process.stdout.write(`${JSON.stringify(payload, null, 2)}\n`);
