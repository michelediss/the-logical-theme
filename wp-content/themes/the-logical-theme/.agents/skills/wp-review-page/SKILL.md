---
name: wp-review-page
description: "Review one generated WordPress page using Figma and local screenshots, with a single automatic correction pass."
---

# wp-review-page

Use this skill only for one page whose deterministic inputs already exist.

## Required inputs

- `ai-source/<page>/manifest.json`
- `ai-source/<page>/code/wp-draft/`
- `ai-source/<page>/screen-figma/`
- `ai-source/<page>/screen-wp/`
- `wp-content/themes/the-logical-theme/docs/block/whitelisted-blocks.md`
- `references/figma-make-architecture.md`

Validate preconditions with `scripts/validate-review-inputs.sh`.

## What this skill does

- compares WordPress screenshots with Figma screenshots
- identifies high-value mismatches
- applies at most one automatic revision pass
- writes reviewed output and a reasoned report

## What this skill must not do

- do not start a second automatic revision pass
- do not ignore `review_auto_pass_used`
- do not chase pixel parity beyond theme and whitelist constraints
- do not optimize Lighthouse in this skill

## Required reading order

1. `wp-content/themes/the-logical-theme/docs/block/whitelisted-blocks.md`
2. `references/figma-make-architecture.md`
3. `ai-source/<page>/manifest.json`
4. Figma screenshots
5. local WordPress screenshots
6. current WordPress draft code

## Output contract

Write or update:

- `ai-source/<page>/code/wp-reviewed/`
- `ai-source/<page>/reports/review-page.md`

The report must include:

- prioritized mismatches
- ignored mismatches with reason
- corrections applied in the single auto-pass
- items deferred to manual review

If `review_auto_pass_used` is already true, do not auto-edit again. Produce a report that stops at analysis.
