#!/usr/bin/env node

import { BREAKPOINTS, getArtifactsRoot } from './visual-qa-common.mjs';

const payload = {
  source: 'visual-qa-common.mjs',
  compare_mode: 'llm_screenshot_review_with_metrics',
  artifacts_root: getArtifactsRoot(),
  breakpoints: BREAKPOINTS,
  max_iterations: 3,
  performance_audit: {
    enabled: true,
    provider: 'lighthouse',
    gate: 'soft',
    authoritative_profile: 'mobile',
    thresholds: {
      performance_score: 0.75,
      lcp_ms: 2500,
      cls: 0.1,
      inp_ms: 200,
    },
  },
};

process.stdout.write(`${JSON.stringify(payload, null, 2)}\n`);
