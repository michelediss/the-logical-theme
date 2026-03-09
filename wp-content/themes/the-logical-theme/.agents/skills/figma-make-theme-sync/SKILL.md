---
name: figma-make-theme-sync
description: Generate Gutenberg-compatible theme artifacts for the-logical-theme from repository-local figma.json using Figma Make.
allowed-tools:
  - functions.exec_command
  - functions.list_mcp_resources
  - functions.list_mcp_resource_templates
  - functions.read_mcp_resource
  - functions.mcp__figma__get_design_context
  - functions.mcp__figma__get_screenshot
---

# Figma Make Theme Sync

Use this skill when the user wants Gutenberg-compatible code for `the-logical-theme` based on Figma Make.

`SKILL.md` is the human entrypoint. `skill.yaml` is the structured contract for inputs, outputs, runtime notes, and tool classification.

## Purpose

- read theme runtime facts from the repository instead of restating them from memory
- inspect the Make app through MCP before generating Gutenberg output
- generate or update `theme.json`, patterns, parts, and templates in the smallest shape that matches the design intent
- finish page-oriented work with the repository visual QA loop and Lighthouse performance audit

## Required Preconditions

Stop immediately if any of these conditions is not true:

- `figma.json` exists in the theme root
- `figma.source` equals `"make"`
- `figma.app_url` is a valid `https://www.figma.com/make/...` URL
- `mcp__figma__get_design_context` is available
- `mcp__figma__get_screenshot` is available

This skill has no fallback mode without MCP Figma.

## Runtime Sources Of Truth

Read runtime facts from these sources before generating code:

- `figma.json`
- `partials/block-availability.php`
- `partials/block-availability/runtime.php`
- `inc/blocks.php`
- `blocks/*/block.json`
- `.agents/skills/figma-make-theme-sync/scripts/export-theme-blocks.php`
- `.agents/skills/figma-make-theme-sync/scripts/export-theme-tokens.mjs`
- `.agents/skills/figma-make-theme-sync/scripts/export-visual-qa-config.mjs`
- `.agents/skills/figma-make-theme-sync/scripts/get-figma-app-url.sh`
- `.agents/skills/figma-make-theme-sync/scripts/capture-figma-make-screenshots.mjs`
- `.agents/skills/figma-make-theme-sync/scripts/capture-wp-screenshots.mjs`
- `.agents/skills/figma-make-theme-sync/scripts/run-lighthouse-audit.mjs`
- `.agents/skills/figma-make-theme-sync/scripts/resume-visual-qa-run.mjs`
- `.agents/skills/figma-make-theme-sync/scripts/prepare-visual-qa-report.mjs`

During development, always read:

- `docs/theme-overview.md`
- `docs/allowed-blocks.md`
- `docs/custom-blocks.md`

When the task depends on the Make app implementation, read `references/figma-make-architecture.md` after MCP has returned real Make context.

## Workflow

1. Confirm the theme structure in `patterns/`, `parts/`, `templates/`, and `inc/`.
2. Read the required docs context from `docs/`.
3. Validate `figma.json` and resolve URLs from `scripts/get-figma-app-url.sh`.
4. Export runtime block availability and visual QA configuration with the local scripts.
5. Inspect current `theme.json` state only when the task requires token updates or merges.
6. Start from `figma.app_url` and call `mcp__figma__get_design_context`.
7. Call `mcp__figma__get_screenshot` for the same Make app context.
8. Review returned Make sources in this order: `package.json`, `src/app/App.tsx`, `src/styles/index.css`, `src/styles/theme.css`, `src/styles/fonts.css`, representative section files, representative UI primitive files, imported content files.
9. Generate `theme.json` suggestions first when the task is page-oriented or system-oriented.
10. Build reusable `patterns/*.php` before composing templates.
11. Build `parts/*.html` when the page needs shared structural regions.
12. Compose `templates/*.html` from patterns and parts instead of writing monolithic markup.
13. Run WordPress screenshot capture for the current iteration.
14. Run Lighthouse against the same WordPress URL for the current iteration.
15. If Lighthouse is below threshold, apply low-risk remediation to the generated code, then repeat WordPress capture and Lighthouse until the page passes or the default 3-iteration loop is exhausted.
16. If the result is still unsatisfactory, continue on the same `run_id` with a resume cycle that appends `iter-4+`.
17. On resume, let the user choose `reuse` or `refresh` for the Figma baseline. `refresh` means rerun both MCP design context and the Figma screenshot baseline.
18. Finish page-oriented work with the visual QA and performance reports.

## Hard Rules

- Runtime data from PHP, JSON, and scripts wins over docs when they disagree.
- `export-theme-blocks.php` must bootstrap WordPress so the block registry and saved availability settings reflect real runtime state.
- In this repository, prefer running the block export through the local `wpcli` container so the script sees the same PHP extensions and database-backed state as WordPress.
- Never invent node ids, frame ids, or alternate Figma URLs.
- Always start from `figma.app_url`.
- Treat `mcp__figma__get_design_context` as the primary Make source inspection path.
- Treat `mcp__figma__get_screenshot` as mandatory visual context, not as an optional convenience.
- Do not rely on `mcp__figma__get_metadata` or `mcp__figma__get_variable_defs` for this skill.
- Use MCP resource listing and reading tools only as support when linked resources need inspection; they do not replace the required Figma MCP tools.
- `theme.json` is only for stable global tokens and Gutenberg-facing defaults.
- Keep `settings.layout` in `theme.json`.
- Prefer Gutenberg presets and block supports over copied Tailwind utility classes.
- For page-oriented work, do not finish without `theme.json` output or update guidance, at least one reusable pattern or part, at least one named template artifact, screenshot capture, a shared `run_id`, a visual QA report, and a Lighthouse performance report.
- Treat Lighthouse as lab-data validation of the generated WordPress page, not as field data or as a Figma Make audit.
- Use a soft gate for performance: do not claim a full pass while mobile Lighthouse remains below threshold, but prefer warning or max-iterations status over destructive layout changes.
- Auto-remediation is allowed only for low-risk fixes such as image loading hints, preload/preconnect, asset deferral, missing dimensions, or similarly local changes tied to the generated output.
- Use `run-manifest.json` as the source of truth for current iteration, baseline generations, and resume history on a shared `run_id`.
