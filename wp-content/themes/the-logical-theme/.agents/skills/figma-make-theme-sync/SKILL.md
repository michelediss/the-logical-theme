---
name: figma-make-theme-sync
description: Generate Gutenberg-compatible theme.json suggestions, block patterns, template parts, and templates for the-logical-theme from a repository-local figma.json that points to a Figma Make app URL. Use this skill when the user wants WordPress block-theme code derived from Figma Make context without storing node ids or frame URLs.
---

# Figma Make Theme Sync

Use this skill when the user wants Gutenberg-compatible code for `the-logical-theme` based on Figma Make.

This skill is template-first and includes an iterative visual QA loop driven by screenshots. When the task is page-oriented, the minimum acceptable output is at least one working file in `templates/` plus a matching visual validation pass.

This skill is repository-local on purpose. It must read the theme's real files before generating anything:

- `figma.json`
- `theme.json`
- `inc/patterns.php`
- `patterns/*.php`
- `parts/*.html`
- `templates/*.html`
- `ai-rules/THEME_SUMMARY.md`
- `ai-rules/AI_AVAILABLE_BLOCKS.md`
- `.artifacts/visual-qa/` when the task includes validation history
- page mappings in `figma.json` when page-specific Figma/site URL relationships are configured

## Repository assumptions

Treat the directory containing `figma.json` as the theme root. In this repository that root is the same directory that contains:

- `theme.json`
- `patterns/`
- `parts/`
- `templates/`
- `inc/`

Do not read Figma configuration from any other file.

## When to use

Use this skill for requests such as:

- generate a hero pattern from the figma make app
- generate archive.html from figma context
- propose theme.json tokens based on figma UI
- map a Figma Make design into a Gutenberg template part for this theme

Do not use this skill for generic React exports, custom block development, or plugin architecture unless the user explicitly asks for that.

## Workflow

1. Confirm the theme structure by checking `patterns/`, `parts/`, `templates/`, `inc/`, and `theme.json`.
2. Read the current theme conventions from:
   - `ai-rules/THEME_SUMMARY.md`
   - `ai-rules/AI_AVAILABLE_BLOCKS.md`
   - existing files in the target output directory
3. Resolve the Figma Make URL by running `scripts/get-figma-app-url.sh`.
4. Validate that `figma.json` contains:
   - `figma.source` equal to `make`
   - `figma.app_url` as the global Make app source
   - optional `figma.pages` mappings for page-specific Figma/site URL relationships
5. Parse the Make app/file identifier from `figma.app_url`.
6. Retrieve Figma context through MCP using the Make app as the source of truth.
7. Capture reference screenshots from Figma Make for all five Tailwind breakpoints before finalizing code.
8. Generate only one of these target types unless the user explicitly asks for multiple:
   - `theme.json` suggestions
   - `patterns/*.php`
   - `parts/*.html`
   - `templates/*.html`
9. When the task is page-oriented, always finish with at least one concrete `templates/*.html` artifact named by the user.
10. Before writing code, compare the generated structure against the theme's existing conventions and the Gutenberg block whitelist below.
11. Prefer the smallest change that matches the design intent.
12. Capture output screenshots from WordPress, compare them against the Figma reference set, then iterate on the generated code until the comparison is satisfactory or the loop reaches the hard stop.

## Required task inputs

Use these inputs when the skill is invoked:

- `task`: natural-language generation request
- `target_type`: one of `theme.json`, `pattern`, `part`, or `template`
- `target_name`: required for template work; the user explicitly names the template to generate, for example `page`, `front-page`, or a custom page template slug
- `page_key`: optional logical key that resolves a configured pair of `figma_url` and `site_url` from `figma.json`
- `output_path`: optional explicit destination path inside the theme
- `preview_url`: optional override for the WordPress page URL used for output screenshots; it wins over any configured site mapping

Do not assume a default page template. If the user says "sviluppa il template X", treat `X` as mandatory input and generate that template.

## Configuration rules

Expected config:

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

Validation rules:

- `figma.json` must exist in the repository root
- the JSON must parse cleanly
- `figma` must be an object
- `figma.source` must equal `"make"`
- `figma.app_url` must be a non-empty `https://www.figma.com/make/...` URL
- if `figma.pages` is provided, it must be an object keyed by logical page names
- each `figma.pages.<key>` entry must provide non-empty `figma_url` and `site_url`
- each mapped `figma_url` and `site_url` must be a valid HTTP/HTTPS URL

Error handling:

- If `figma.json` is missing, stop and tell the user to create it in the repository root.
- If `figma.source` is missing or not `make`, stop and say this skill only supports Figma Make.
- If `figma.app_url` is missing or malformed, stop and show the expected URL shape.
- If `figma.pages` is malformed, stop and report the exact invalid key.
- Never fall back to node ids, frame URLs, or view URLs.

## MCP integration

Use `figma.app_url` as the global MCP configuration input.

Preferred MCP sequence:

1. Use the URL from `scripts/get-figma-app-url.sh`.
2. Derive the Make file/app key from that URL.
3. Request whole-app or document-level Figma context through MCP.
4. If more structure is needed, use metadata, variables, or screenshots from the same Make app context.

Preferred Figma MCP tools:

- `mcp__figma__get_design_context`
- `mcp__figma__get_metadata`
- `mcp__figma__get_variable_defs`
- `mcp__figma__get_screenshot`

Rules:

- Never assume a stored node id.
- Never require frame selection from repository config.
- Always start from `figma.app_url`.
- If MCP cannot provide usable context from the Make app URL alone, stop with a clear explanation instead of inventing missing identifiers.

## Page URL mappings

`figma.json` can persist logical relationships between a Figma Make page URL and a site page URL.

Use named mappings under `figma.pages`, for example:

- `home`
- `about`
- `pricing`

Each mapping may include:

- `figma_url`: page-specific Figma Make URL for screenshot capture
- `site_url`: page-specific WordPress URL for output capture
- `target_name`: optional template slug hint
- `label`: optional human-readable label

Resolution priority:

1. `preview_url` from the task overrides the mapped site URL
2. if `page_key` is present, use `figma.pages.<page_key>.figma_url` and `figma.pages.<page_key>.site_url`
3. if no `page_key` exists, use `figma.app_url` for Figma capture
4. if no site URL can be resolved, stop and ask for an explicit preview URL or a valid mapping

When a page mapping exists, prefer it for screenshot-based visual QA rather than making the user repeat raw URLs in every prompt.

## Visual QA loop

The skill must validate generated code visually before considering the task complete.

### Breakpoints

Use exactly these five Tailwind breakpoints for every capture set:

- `sm`: width `640`
- `md`: width `768`
- `lg`: width `1024`
- `xl`: width `1280`
- `2xl`: width `1536`

Use a consistent browser height of `1600` and save full-page screenshots.

### Artifact location

Store every visual QA artifact under:

- `wp-content/themes/the-logical-theme/.artifacts/visual-qa/<target-name>/<timestamp>/`

Expected structure:

- `input/<breakpoint>.png`
- `output/iter-1/<breakpoint>.png`
- `output/iter-2/<breakpoint>.png`
- `output/iter-3/<breakpoint>.png`
- `diff/iter-N/`
- `reports/iter-N.json`
- `reports/iter-N.md`
- `reports/final-summary.md`

### Capture tooling

Use the bundled Playwright scripts:

- `scripts/capture-figma-make-screenshots.mjs`
- `scripts/capture-wp-screenshots.mjs`
- `scripts/prepare-visual-qa-report.mjs`
- `scripts/get-figma-app-url.sh --field app_url|figma_url|site_url [--page-key KEY]`

Run them from the theme root so their relative paths resolve correctly.

### Loop rules

Follow this loop for template-oriented tasks:

1. Capture Figma Make reference screenshots for the five breakpoints.
2. Generate or update the target template and any supporting patterns, parts, or `theme.json` values required to match the design.
3. Capture WordPress screenshots for the same five breakpoints.
4. Prepare a comparison report for the current iteration.
5. Compare input and output screenshots and identify concrete mismatches in structure, spacing, typography scale, media treatment, and CTA placement.
6. If the result is satisfactory, stop and preserve the final screenshots and report.
7. Otherwise, revise the generated code and repeat the cycle.

Hard stop:

- maximum `3` iterations

Completion rule:

- the task is complete only when the loop reaches a satisfactory comparison or the third iteration finishes with an explicit report of residual mismatches

### Comparison policy

The comparison is LLM-guided, not pixel-perfect.

When reviewing screenshots, classify issues at minimum as:

- `layout_spacing`
- `content_hierarchy`
- `typography_scale`
- `media_crop_or_size`
- `cta_navigation_placement`

Treat the result as satisfactory only when no structural or obviously responsive mismatch remains.

## Code generation targets

### `theme.json` suggestions

Generate additive or replacement suggestions for:

- color palette
- typography presets
- spacing scale
- layout widths
- block-level defaults
- template part metadata when relevant

Return valid `theme.json` fragments or a full replacement only if the user explicitly asks for it.

### `patterns/*.php`

Generate block patterns using `register_block_pattern(...)` and heredoc `content`.

Rules:

- use the theme category `the-logical-theme`
- use the textdomain `the-logical-theme`
- keep PHP minimal
- generate static block markup inside the `content` string
- align naming with existing pattern files

### `parts/*.html`

Generate HTML block template parts only.

Rules:

- use block comment syntax
- use shared site/theme blocks only where appropriate
- keep structure editor-friendly

### `templates/*.html`

Generate HTML block templates only.

Rules:

- prefer `core/template-part` for shared areas
- use dynamic blocks only for archive, single, search, or other WordPress-driven content
- keep the template compatible with Site Editor conventions
- for page-oriented tasks, this is the minimum required final artifact

## Theme constraints

Respect these conventions at all times:

- theme architecture is block-first
- patterns are registered through `inc/patterns.php`
- templates and template parts are HTML block templates
- PHP rendering should be minimal
- PHP function prefix: `the_logical_theme_`
- asset handle prefix: `the-logical-theme-`
- textdomain: `the-logical-theme`

Prefer:

- `theme.json` settings over ad hoc inline styles when the design maps cleanly to theme tokens
- patterns for reusable sections
- template parts for shared structural regions
- plain block composition over custom PHP

## Gutenberg block whitelist

Generate only these blocks unless the user explicitly requests otherwise.

### Layout / Content

- `core/group`
- `core/columns`
- `core/column`
- `core/spacer`
- `core/separator`
- `core/heading`
- `core/paragraph`
- `core/list`
- `core/list-item`
- `core/quote`
- `core/details`
- `core/image`
- `core/gallery`
- `core/cover`
- `core/media-text`
- `core/buttons`
- `core/button`
- `core/accordion`
- `core/accordion-item`
- `core/accordion-heading`
- `core/accordion-panel`
- `core/social-links`
- `core/social-link`
- `core/search`

### Theme / Site

- `core/navigation`
- `core/navigation-link`
- `core/navigation-submenu`
- `core/home-link`
- `core/template-part`
- `core/site-logo`
- `core/site-title`
- `core/site-tagline`

### Dynamic / Post

- `core/query`
- `core/post-template`
- `core/query-title`
- `core/query-total`
- `core/query-no-results`
- `core/query-pagination`
- `core/query-pagination-previous`
- `core/query-pagination-numbers`
- `core/query-pagination-next`
- `core/post-title`
- `core/post-content`
- `core/post-excerpt`
- `core/post-date`
- `core/post-featured-image`
- `core/post-terms`
- `core/post-navigation-link`

If the current theme contains blocks outside this list, do not copy them forward unless the user explicitly asks to keep them.

## Gutenberg composition rules

- Start with `core/group` as the default layout wrapper.
- Use `core/columns` only when the design clearly needs side-by-side content.
- Prefer `core/media-text` over manual columns for simple media-plus-copy layouts.
- Use `core/spacer` sparingly; prefer token-driven spacing from `theme.json`.
- Use `core/template-part` for shared header, footer, hero, or page-heading regions.
- In templates, use `core/query` and related post blocks only for dynamic contexts.
- In patterns, prefer static content placeholders and editor-friendly defaults.
- Avoid custom classes unless they are necessary and consistent with existing theme conventions.
- Avoid inline style noise when the same result can be expressed through presets or block attributes.

## Coding rules

- Read existing target files before generating a new file in the same category.
- Match the style of the nearest existing example in this theme.
- Keep output ASCII unless the source content requires another character.
- Do not add custom Gutenberg blocks unless explicitly requested.
- Do not create PHP render callbacks unless the editor cannot express the requirement and the user approves.
- Keep explanations concise and focus on the generated artifact and any assumptions taken from Figma context.

## Prompt examples

- `generate a hero pattern from the figma make app`
- `generate archive.html from figma context`
- `propose theme.json tokens based on figma UI`
- `create a footer template part from the figma make app using only allowed blocks`
- `turn the current figma make layout into a reusable CTA pattern for this theme`
- `sviluppa il template page usando il contesto figma make e valida il risultato con screenshot responsive`
- `generate front-page.html, capture figma and wordpress screenshots, then iterate until visual QA is satisfactory`
- `sviluppa il template front-page usando la page_key home definita in figma.json`

## Safety rules

- Never assume a node-id.
- Never require frame selection.
- Always use `figma.app_url`.
- Never generate custom Gutenberg blocks unless explicitly requested.
- Prefer patterns and block composition over custom PHP rendering.
- Never skip screenshot capture and comparison for template-oriented tasks.
- Never declare success without a final visual QA report.
- Never ignore an explicit `preview_url`; it has higher priority than a mapped site URL.
