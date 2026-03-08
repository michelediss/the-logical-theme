---
name: figma-make-theme-sync
description: Generate Gutenberg-compatible theme.json suggestions, block patterns, template parts, and templates for the-logical-theme from a repository-local figma.json that points to a Figma Make app URL. Use this skill when the user wants WordPress block-theme code derived from Figma Make context without storing node ids or frame URLs.
---

# Figma Make Theme Sync

Use this skill when the user wants Gutenberg-compatible code for `the-logical-theme` based on Figma Make.

This skill is tool-driven. It should alternate short prompts with repository-local scripts so runtime facts come from the theme itself instead of from hardcoded markdown.

## Runtime Sources Of Truth

Read runtime facts from these files and scripts, in this order:

- `figma.json`
- `partials/block-availability.php`
- `inc/blocks.php`
- `blocks/*/block.json`
- `.agents/skills/figma-make-theme-sync/scripts/export-theme-blocks.php`
- `.agents/skills/figma-make-theme-sync/scripts/export-theme-tokens.mjs`
- `.agents/skills/figma-make-theme-sync/scripts/export-visual-qa-config.mjs`
- `.agents/skills/figma-make-theme-sync/scripts/get-figma-app-url.sh`
- `.agents/skills/figma-make-theme-sync/scripts/capture-figma-make-screenshots.mjs`
- `.agents/skills/figma-make-theme-sync/scripts/capture-wp-screenshots.mjs`
- `.agents/skills/figma-make-theme-sync/scripts/prepare-visual-qa-report.mjs`

Do not restate block lists or breakpoint values from memory when the scripts above can resolve them.

`theme.json` is not a primary design input of this skill. When the skill reads it through `export-theme-tokens.mjs`, treat it only as the current theme state to compare against or update.

## Development Context

During the development phase, always read all of these Markdown files before generating code:

- `docs/theme-overview.md`
- `docs/allowed-blocks.md`
- `docs/custom-blocks.md`

Use them as development guidance:

- `docs/theme-overview.md` explains the theme architecture and naming conventions.
- `docs/allowed-blocks.md` explains when to use allowed blocks.
- `docs/custom-blocks.md` explains how custom theme blocks are structured and discovered.

Rule:

- runtime data from PHP/JSON/scripts wins over docs when they disagree
- docs still must be read because they define theme conventions and maintenance expectations
- if docs and runtime conflict, call out the mismatch explicitly

When the task depends on understanding how a Figma Make app is implemented, also read:

- `references/figma-make-architecture.md`

Use it only after you have retrieved the Make source through MCP. It documents which parts of the Make stack are usually safe to translate directly into Gutenberg and which parts should be treated as implementation noise.

## Workflow

1. Confirm the theme structure by checking `patterns/`, `parts/`, `templates/`, and `inc/`.
2. Read the development context from all Markdown files in `docs/`.
3. Validate `figma.json` and resolve URLs through `scripts/get-figma-app-url.sh` and `visual-qa-common.mjs`.
4. Export runtime block availability with `scripts/export-theme-blocks.php`.
5. Inspect the current `theme.json` state with `scripts/export-theme-tokens.mjs` only when you need to update or merge existing tokens.
6. Export visual QA configuration with `scripts/export-visual-qa-config.mjs`.
7. Retrieve Figma context through MCP using `figma.app_url` as the source of truth.
8. Generate `theme.json` suggestions first when the task is page-oriented or system-oriented.
9. Use the allowed blocks to build `patterns/*.php` for reusable sections before composing templates.
10. Build `parts/*.html` when the page needs shared structural regions.
11. Compose `templates/*.html` from those patterns and parts instead of writing monolithic template markup.
12. For page-oriented work, always finish with:
   - `theme.json` output or update suggestions
   - at least one reusable pattern or part
   - at least one concrete `templates/*.html` artifact named by the user
13. Before writing code, compare the proposed structure against:
   - exported block availability
   - current theme token state when applicable
   - existing files in the destination directory
   - conventions in `docs/`
14. Prefer the smallest change that matches the design intent.
15. Run the visual QA loop with screenshots, metrics, and semantic review before considering the task complete.

## Required Task Inputs

Use these inputs when the skill is invoked:

- `task`: natural-language generation request
- `target_type`: one of `theme.json`, `pattern`, `part`, or `template`
- `target_name`: required for page-oriented template work; the user explicitly names the template to generate, for example `page`, `front-page`, or a custom page template slug
- `run_id`: required for the screenshot-driven visual QA loop; all capture and report steps must share it
- `page_key`: optional logical key that resolves a configured pair of `figma_url` and `site_url` from `figma.json`
- `output_path`: optional explicit destination path inside the theme
- `preview_url`: optional override for the WordPress page URL used for output screenshots; it wins over any configured site mapping

Do not assume a default page template. If the user says "sviluppa il template X", treat `X` as mandatory input and generate that template.

## Configuration Rules

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

## MCP Integration

Use `figma.app_url` as the global MCP configuration input.

Preferred MCP sequence:

1. Use the URL from `scripts/get-figma-app-url.sh`.
2. Derive the Make file/app key from that URL.
3. Request whole-app Figma Make context through `mcp__figma__get_design_context`.
4. Read `package.json`, `src/app/App.tsx`, `src/styles/*.css`, and representative section components from the returned resource links before generating Gutenberg output.
5. Use the source tree from `get_design_context` as the primary architecture signal for Make files.
6. Only use screenshots from the same Make app context when source inspection is insufficient.

Preferred Figma MCP tools:

- `mcp__figma__get_design_context`
- `mcp__figma__get_screenshot`

Rules:

- Never assume a stored node id.
- Never require frame selection from repository config.
- Always start from `figma.app_url`.
- For Figma Make files, do not rely on `mcp__figma__get_metadata` or `mcp__figma__get_variable_defs`; they are not the primary path and may be unsupported.
- Treat `package.json`, `App.tsx`, shared UI primitives, and global CSS as better signals of implementation architecture than individual section files.
- If MCP cannot provide usable context from the Make app URL alone, stop with a clear explanation instead of inventing missing identifiers.

## Make Source Review

When `mcp__figma__get_design_context` returns Figma Make source files, review them in this order:

1. `package.json`
2. `src/app/App.tsx`
3. `src/styles/index.css`
4. `src/styles/theme.css`
5. `src/styles/fonts.css`
6. representative files under `src/app/components/`
7. representative files under `src/app/components/ui/`
8. imported content files under `src/imports/`

Use that review to distinguish:

- stack facts: framework, build tool, styling system, icon system, UI primitive libraries
- architecture facts: section composition, state usage, data locality, asset conventions
- conversion guidance: what should become `theme.json`, patterns, parts, templates, block styles, or editor content

Do not mirror the React component tree 1:1 into Gutenberg. Translate it by intent:

- app-level shell becomes template + template parts
- page sections become patterns or locked pattern-like template regions
- inline arrays of content become editor-managed blocks or Query Loop data sources
- CSS custom properties become `theme.json` presets where stable
- purely interactive local state stays out of block markup unless the WordPress experience genuinely needs it

## Page URL Mappings

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

## Visual QA Loop

The skill must validate generated code visually before considering the task complete.

Use the runtime visual QA config exported by `scripts/export-visual-qa-config.mjs`.

Rules:

- do not hardcode viewport sizes in the prompt if the export script can provide them
- save full-page screenshots
- store artifacts under `.artifacts/visual-qa/<target-name>/<run-id>/`
- use the report JSON as the objective record of each iteration
- use only canonical CLI flags when invoking the scripts:
  - `--run-id`
  - `--preview-url`
  - `--figma-capture-url`

Expected artifact structure:

- `input/<breakpoint>.png`
- `output/iter-1/<breakpoint>.png`
- `output/iter-2/<breakpoint>.png`
- `output/iter-3/<breakpoint>.png`
- `diff/iter-N/`
- `reports/iter-N.json`
- `reports/iter-N.md`
- `reports/final-summary.md`

### Loop Rules

Follow this loop for template-oriented tasks:

1. Capture Figma Make reference screenshots for the configured breakpoints.
2. Generate or update `theme.json` values required to match the design.
3. Generate or update the supporting patterns and parts required by the page.
4. Compose the target template from those patterns and parts.
5. Capture WordPress screenshots for the same breakpoints.
6. Prepare a comparison report for the current iteration.
7. Review objective metrics first:
   - missing files
   - viewport metadata
   - PNG dimensions
   - SHA-256 hashes
   - byte deltas
8. Review semantic mismatches second:
   - `layout_spacing`
   - `content_hierarchy`
   - `typography_scale`
   - `media_crop_or_size`
   - `cta_navigation_placement`
9. If the result is satisfactory, stop and preserve the final screenshots and report.
10. Otherwise, revise `theme.json`, patterns, parts, or template composition as needed and repeat the cycle.

When invoking the scripts manually, use one shared run id across the loop, for example:

```bash
npm run visual-qa:figma -- --page-key home --target-name front-page --run-id home-qa-1
npm run visual-qa:wp -- --page-key home --target-name front-page --run-id home-qa-1 --iteration 1
npm run visual-qa:report -- --target-name front-page --run-id home-qa-1 --iteration 1
```

Hard stop:

- maximum `3` iterations

Completion rule:

- the task is complete only when the loop reaches a satisfactory comparison or the third iteration finishes with an explicit report of residual mismatches

The comparison remains AI-assisted, but the report must contain objective metrics before semantic review.

Real validation rules:

- treat WordPress capture as the minimum required end-to-end browser validation
- treat Figma capture as valid only when all configured breakpoints complete
- if Figma capture fails or times out on a specific breakpoint, preserve partial artifacts and report the exact breakpoint instead of hanging silently

## Code Generation Targets

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
- for page-oriented tasks, this artifact must be composed from generated patterns and parts whenever the layout can be decomposed that way

## Theme Constraints

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
- templates as orchestration layers over patterns and parts, not as the first place where sections are authored

When deciding which blocks are available, use the JSON export from `scripts/export-theme-blocks.php` instead of any hardcoded list in this file.
