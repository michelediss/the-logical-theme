# figma-make-theme-sync

Local skill for `the-logical-theme` that generates Gutenberg code from `figma.json` using Figma Make as the source and validates the result with responsive screenshots.

The skill is repository-local on purpose and should derive runtime facts from the theme itself, not from duplicated markdown lists.

## What It Does

- reads `figma.app_url` from `figma.json`
- resolves optional `figma.pages` mappings for Figma and site URLs
- exports runtime block availability from the theme
- can inspect the current `theme.json` state when updating an existing theme
- exports the visual QA viewport configuration used by the capture scripts
- retrieves Figma Make context through MCP, using Make source inspection to understand what should be converted
- generates or updates `theme.json`, patterns, template parts, and templates
- for page-oriented tasks, builds `theme.json` first, then patterns/parts, then `templates/*.html`
- uses Playwright-based capture scripts to collect input/output screenshots and prepare a structured comparison report

## Verified Tool Split

- `Figma MCP` is the source-inspection layer for Figma Make. Use `get_design_context` to inspect source files, stack, section structure, and asset references.
- `Playwright` is the screenshot layer. Use the repository capture scripts for Figma reference captures, WordPress output captures, and visual QA reports.

This distinction matters for Figma Make:

- `get_metadata` is not the primary inspection path for Make files
- `get_design_context` is the MCP tool that exposes the useful Make app structure
- end-to-end visual validation should continue through Playwright

Diagnostic note:

- `.artifacts/figma-mcp-debug/` is debug-only and is not part of the normal Make-to-Gutenberg generation pipeline

## Required Development Context

Before generating code, always read:

- `docs/theme-overview.md`
- `docs/allowed-blocks.md`
- `docs/custom-blocks.md`

Interpretation rule:

- runtime data from code and JSON files is authoritative
- docs are required development context and usage guidance
- if docs and runtime disagree, report the mismatch explicitly

## Runtime Sources Of Truth

Primary sources:

- `figma.json`
- `partials/block-availability.php`
- `inc/blocks.php`
- `blocks/*/block.json`
- `.agents/skills/figma-make-theme-sync/scripts/visual-qa-common.mjs`

Machine-readable exports:

- `npm run skill:export-blocks`
- `npm run skill:export-tokens`
- `npm run skill:export-visual-qa`

`theme.json` is not a primary design input for this skill. When it is read, it should be treated as the current theme state to compare against or update, not as the source that drives generation.

## Expected Inputs

- `task`: natural-language request
- `target_type`: `theme.json`, `pattern`, `part`, `template`
- `target_name`: target slug, required for templates
- `run_id`: shared visual QA run identifier, required for the documented multi-step screenshot workflow
- `page_key`: optional logical key to use a mapping defined in `figma.json`
- `output_path`: optional explicit path
- `preview_url`: optional WordPress URL for output capture, with higher priority than the configured mapping

## Configuration

The skill only uses `figma.json` in the theme root:

```json
{
  "figma": {
    "source": "make",
    "app_url": "https://www.figma.com/make/APP_ID",
    "pages": {
      "home": {
        "label": "Home page",
        "target_name": "front-page",
        "figma_url": "https://www.figma.com/make/APP_ID?screen=home",
        "site_url": "http://thelogicaltheme.localhost/"
      }
    }
  }
}
```

Resolution priority is:

1. `preview_url`
2. `figma.pages.<page_key>.site_url`
3. explicit error if no site URL can be resolved

For Figma screenshots:

1. explicit `--figma-capture-url`
2. `figma.pages.<page_key>.figma_url`
3. `figma.app_url`

## Included Scripts

- `scripts/get-figma-app-url.sh`
- `scripts/export-theme-blocks.php`
- `scripts/export-theme-tokens.mjs`
- `scripts/export-visual-qa-config.mjs`
- `scripts/capture-figma-make-screenshots.mjs`
- `scripts/capture-wp-screenshots.mjs`
- `scripts/prepare-visual-qa-report.mjs`

Theme npm scripts:

```bash
npm run skill:export-blocks
npm run skill:export-tokens
npm run skill:export-visual-qa
npm run visual-qa:figma -- --page-key home --target-name front-page --run-id home-qa-1
npm run visual-qa:wp -- --page-key home --target-name front-page --run-id home-qa-1 --iteration 1
npm run visual-qa:report -- --target-name front-page --run-id home-qa-1 --iteration 1
./.agents/skills/figma-make-theme-sync/scripts/get-figma-app-url.sh --field site_url --page-key home
```

## Breaking Changes

- The visual QA loop now requires `--run-id` for every capture/report step.
- The WordPress capture script accepts only `--preview-url` as explicit URL override.
- The Figma capture script accepts only `--figma-capture-url` as explicit URL override.
- Legacy CLI aliases such as `--url` and `--figma-url` are rejected with migration errors.

## Visual QA

Use the breakpoints exported by `scripts/export-visual-qa-config.mjs`.

Artifacts:

- `.artifacts/visual-qa/<target-name>/<run-id>/input/`
- `.artifacts/visual-qa/<target-name>/<run-id>/output/iter-N/`
- `.artifacts/visual-qa/<target-name>/<run-id>/diff/iter-N/`
- `.artifacts/visual-qa/<target-name>/<run-id>/reports/`

The report now includes objective metrics before semantic review:

- file presence
- viewport metadata
- PNG dimensions
- SHA-256 hashes
- byte deltas

Semantic review still evaluates at least:

- `layout_spacing`
- `content_hierarchy`
- `typography_scale`
- `media_crop_or_size`
- `cta_navigation_placement`

## Workflow Summary

1. Read the theme structure and required docs context.
2. Validate `figma.json`.
3. Export allowed blocks and visual QA config from the repository, and inspect the current `theme.json` only if the task is updating existing theme tokens.
4. Retrieve Figma Make context through MCP, starting from `get_design_context`.
5. Generate `theme.json` tokens and defaults from the design intent.
6. Build section-level `patterns/*.php` using only allowed blocks.
7. Build shared `parts/*.html` when the page needs reusable structural regions.
8. Compose `templates/*.html` from patterns and parts instead of writing monolithic template markup.
9. Choose a shared `run_id` for the full visual QA iteration.
10. Capture reference screenshots through the Playwright-based Figma capture script.
11. Capture WordPress screenshots for the composed result through the Playwright-based WordPress capture script.
12. Produce a comparison report with objective metrics.
13. Use AI only for layout mapping and semantic mismatch analysis.
14. Correct and repeat until the result is satisfactory or the 3-iteration limit is reached.

## Real Validation

- WordPress capture can be validated end-to-end against `thelogicaltheme.localhost`.
- Figma Make capture should use a real Make URL, not the placeholder `APP_ID` in sample config.
- Figma Make code understanding should come from MCP source inspection, not from screenshot-only inference.
- Figma capture is considered successful only when all configured breakpoints complete.
- If Figma stalls on a specific breakpoint, the script should fail explicitly and preserve partial artifacts for diagnosis.

## Notes

- The skill does not use node IDs persisted in the repository.
- For page-oriented tasks it cannot declare success without a final visual QA report and a composed output that includes theme tokens plus reusable pattern/part building blocks.
- Running the browser scripts requires Playwright to be installed in the theme.
- If `page_key` exists, the skill uses the mappings configured in `figma.json` for Figma screenshots and site preview.
