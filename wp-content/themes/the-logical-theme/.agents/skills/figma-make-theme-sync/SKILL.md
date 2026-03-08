---
name: figma-make-theme-sync
description: Generate Gutenberg-compatible theme.json suggestions, block patterns, template parts, and templates for the-logical-theme from a repository-local figma.json that points to a Figma Make app URL. Use this skill when the user wants WordPress block-theme code derived from Figma Make context without storing node ids or frame URLs.
---

# Figma Make Theme Sync

Use this skill when the user wants Gutenberg-compatible code for `the-logical-theme` based on Figma Make.

This skill is repository-local on purpose. It must read the theme's real files before generating anything:

- `figma.json`
- `theme.json`
- `inc/patterns.php`
- `patterns/*.php`
- `parts/*.html`
- `templates/*.html`
- `ai-rules/THEME_SUMMARY.md`
- `ai-rules/AI_AVAILABLE_BLOCKS.md`

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
   - `figma.app_url` as the only Figma context source
5. Parse the Make app/file identifier from `figma.app_url`.
6. Retrieve Figma context through MCP using the Make app as the source of truth.
7. Generate only one of these target types unless the user explicitly asks for multiple:
   - `theme.json` suggestions
   - `patterns/*.php`
   - `parts/*.html`
   - `templates/*.html`
8. Before writing code, compare the generated structure against the theme's existing conventions and the Gutenberg block whitelist below.
9. Prefer the smallest change that matches the design intent.

## Configuration rules

Expected config:

```json
{
  "figma": {
    "source": "make",
    "app_url": "https://www.figma.com/make/APP_ID"
  }
}
```

Validation rules:

- `figma.json` must exist in the repository root
- the JSON must parse cleanly
- `figma` must be an object
- `figma.source` must equal `"make"`
- `figma.app_url` must be a non-empty `https://www.figma.com/make/...` URL

Error handling:

- If `figma.json` is missing, stop and tell the user to create it in the repository root.
- If `figma.source` is missing or not `make`, stop and say this skill only supports Figma Make.
- If `figma.app_url` is missing or malformed, stop and show the expected URL shape.
- Never fall back to node ids, frame URLs, or view URLs.

## MCP integration

Use `figma.app_url` as the only configuration input.

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

## Safety rules

- Never assume a node-id.
- Never require frame selection.
- Always use `figma.app_url`.
- Never generate custom Gutenberg blocks unless explicitly requested.
- Prefer patterns and block composition over custom PHP rendering.
