---
name: wp-optimize-lighthouse
description: "Optimize one page from Lighthouse audit artifacts without breaking the design or whitelist constraints."
---

# wp-optimize-lighthouse

Use this skill only after review has reached an acceptable baseline.

## Required inputs

- `ai-source/<page>/manifest.json`
- `ai-source/<page>/code/wp-reviewed/` or current stable page code
- `ai-source/<page>/lighthouse/mobile.json`
- `ai-source/<page>/lighthouse/desktop.json`
- `wp-content/themes/the-logical-theme/docs/block/whitelisted-blocks.md`

Run `scripts/validate-audit-inputs.sh` before reasoning.

## What this skill does

- reads mobile and desktop audit outputs
- prioritizes safe improvements for performance, accessibility, best practices, and SEO
- writes optimized output for one page
- documents what was applied and what was intentionally deferred

## What this skill must not do

- do not regenerate the page from scratch
- do not remove essential structure to chase a score
- do not introduce non-whitelisted blocks
- do not optimize multiple pages in one reasoning pass

## Output contract

Write or update:

- `ai-source/<page>/code/wp-optimized/`
- `ai-source/<page>/reports/lighthouse-optimize.md`

The report must include:

- initial mobile and desktop signals
- safe fixes applied
- fixes rejected because they risk visual or structural regressions
- residual manual work
