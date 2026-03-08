# figma-make-theme-sync

Local skill for `the-logical-theme` that generates Gutenberg code from `figma.json` using Figma Make as the source and validates the result with responsive screenshots.

## What It Does

- reads `figma.app_url` from `figma.json`
- can resolve persistent mappings between Figma Make pages and site pages from `figma.pages`
- retrieves Figma Make context through MCP
- generates or updates `theme.json`, patterns, template parts, and templates
- for page-oriented tasks, produces at least one `templates/*.html`
- captures input/output screenshots at Tailwind breakpoints
- compares results and supports a correction loop of up to 3 iterations

## Expected Inputs

- `task`: natural-language request
- `target_type`: `theme.json`, `pattern`, `part`, `template`
- `target_name`: target slug, required for templates
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

1. explicit `--figma-url`
2. `figma.pages.<page_key>.figma_url`
3. `figma.app_url`

## Visual QA

Breakpoints used:

- `sm`: 640
- `md`: 768
- `lg`: 1024
- `xl`: 1280
- `2xl`: 1536

Screenshots are full-page with viewport height `1600`.

Artifacts:

- `.artifacts/visual-qa/<target-name>/<run-id>/input/`
- `.artifacts/visual-qa/<target-name>/<run-id>/output/iter-N/`
- `.artifacts/visual-qa/<target-name>/<run-id>/diff/iter-N/`
- `.artifacts/visual-qa/<target-name>/<run-id>/reports/`

The comparison is LLM-guided and evaluates at least:

- `layout_spacing`
- `content_hierarchy`
- `typography_scale`
- `media_crop_or_size`
- `cta_navigation_placement`

## Included Scripts

- `scripts/get-figma-app-url.sh`
- `scripts/capture-figma-make-screenshots.mjs`
- `scripts/capture-wp-screenshots.mjs`
- `scripts/prepare-visual-qa-report.mjs`

Theme npm scripts:

```bash
npm run visual-qa:figma -- --page-key home --target-name front-page
npm run visual-qa:wp -- --page-key home --target-name front-page --iteration 1
npm run visual-qa:report -- --target-name page --iteration 1
./.agents/skills/figma-make-theme-sync/scripts/get-figma-app-url.sh --field site_url --page-key home
```

## Workflow Summary

1. Read the theme structure and local conventions.
2. Read the documentation in `docs/`, especially `docs/theme-overview.md` and `docs/allowed-blocks.md`.
3. Validate `figma.json`.
4. Retrieve Figma Make context through MCP.
5. Capture reference screenshots.
6. Generate or update the requested target.
7. Capture WordPress screenshots.
8. Produce a comparison report.
9. Correct and repeat until the result is satisfactory or the 3-iteration limit is reached.

## Notes

- The skill does not use node IDs persisted in the repository.
- For page-oriented tasks it cannot declare success without a final visual QA report.
- Running the browser scripts requires Playwright to be installed in the theme.
- If `page_key` exists, the skill uses the mappings configured in `figma.json` for Figma screenshots and site preview.
