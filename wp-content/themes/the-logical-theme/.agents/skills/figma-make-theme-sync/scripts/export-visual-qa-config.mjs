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
  resume: {
    supported: true,
    strategy: 'same_run_unlimited_iteration',
    baseline_modes: ['reuse', 'refresh'],
    refresh_policy: 'refresh_requires_new_figma_screenshot_and_new_mcp_design_context_in_skill_workflow',
  },
};

process.stdout.write(`${JSON.stringify(payload, null, 2)}\n`);
