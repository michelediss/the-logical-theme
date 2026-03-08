# Figma Make Architecture To Gutenberg

Use this reference only after `mcp__figma__get_design_context` has returned source files for a real Figma Make app.

## Verified MCP Workflow

- Start from `figma.app_url`.
- Extract the Make file key from the `/make/<key>` URL.
- Call `mcp__figma__get_design_context` for the Make file.
- Read the returned resource links, starting with `package.json` and `src/app/App.tsx`.

For Make files, `get_design_context` is the useful MCP path. `get_metadata` is not the primary inspection tool and may be unsupported.

## Observed Stack In `ugHr0Gh5yVbWe2mtIPCLIG`

These facts were verified from MCP source resources, not inferred from screenshots.

- Framework: React 18.3.1
- Bundler: Vite 6
- Styling: Tailwind CSS 4 with `@tailwindcss/vite`
- UI primitives: Radix UI
- Variant utility: `class-variance-authority`
- Class composition: `clsx` + `tailwind-merge`
- Icons: `lucide-react`
- Animation package present: `motion`
- Additional libraries present but not central to the sampled homepage: MUI, Emotion, React Hook Form, Recharts, React DnD, React Router, Sonner, Vaul

Important interpretation:

- Do not assume Redux, Zustand, MobX, or another app-wide state layer unless MCP source files actually show one.
- In this Make app there is no evidence of Redux-style global state in the reviewed files.
- `react-router` is installed but the reviewed homepage is composed inside a single `App.tsx` shell, so routing is not a core architectural signal here.
- MUI and Emotion are installed, but the sampled UI is authored with Tailwind utility classes and Radix-style primitives, not Material UI component composition.

## Observed App Shape

`src/app/App.tsx` composes the page as a flat sequence of sections:

- `SputnikHeader`
- `Hero`
- `NewReleases`
- `FeaturedAuthors`
- `CatalogGrid`
- `IndieScene`
- `Festival`
- `Newsletter`
- `SputnikFooter`

This is a strong Gutenberg hint:

- treat the page as template orchestration
- treat each section as a reusable pattern candidate
- treat header and footer as template parts

Do not convert each React component into a custom block by default.

## Styling Model

The reviewed Make app uses three layers:

1. font variables in `src/styles/fonts.css`
2. theme variables and Tailwind token bridging in `src/styles/theme.css`
3. utility-first layout and spacing in component `className` strings

Practical mapping:

- font families become `theme.json` typography presets
- stable colors become `theme.json` palette entries
- stable radii and spacing values become `theme.json` spacing and border presets
- section-specific one-off values stay in pattern or block style attributes if they do not repeat

Do not copy the entire Tailwind class list into Gutenberg.
Normalize repeated values first.

## Content Model

The reviewed section files keep most content inline:

- navigation links are inline arrays
- books and authors are inline arrays
- some assets come from `figma:asset/...`
- some images and copy come from imported or scraped content sources such as `src/imports/sputnikpress-content.md`

Gutenberg mapping rule:

- inline repeated content arrays should usually become editor-managed blocks, pattern placeholders, CPT-backed loops, or Query Loop data
- do not freeze catalog items as static HTML unless the section is intentionally decorative
- imported markdown or scraped source content is a hint that the Make app may include staging content, not final CMS structure

## Asset Model

The reviewed app mixes:

- local `figma:asset/...` image imports
- remote production URLs from `sputnikpress.it`

WordPress mapping:

- prefer WordPress media attachments or theme assets over hardcoded `figma:asset` references
- when a section clearly represents real site data, prefer dynamic WP sources over static imported URLs
- keep decorative textures or logo assets as theme assets only when they are part of the visual identity rather than editor content

## State And Interactivity

The reviewed header uses local `useState` for the mobile menu and cart badge display.

Conversion rules:

- lightweight presentational state can become native block/editor behavior or small progressive enhancement JS
- do not introduce a custom React frontend bundle for simple menu toggles
- reserve custom interactive blocks for cases where Gutenberg core blocks cannot express the interaction

If the interaction is only mobile navigation disclosure, prefer:

- core/navigation where possible
- a template part structure that remains usable without app-level state

## Gutenberg Translation Heuristics

Use these defaults unless the Make source contradicts them:

- `App.tsx` shell -> `templates/*.html`
- `SputnikHeader`, `SputnikFooter` -> `parts/*.html`
- section components -> `patterns/*.php`
- global tokens from `theme.css` and `fonts.css` -> `theme.json`
- inline content arrays -> pattern placeholders, CPTs, or Query Loop sources
- decorative overlays and gradients -> block styles, group wrappers, cover blocks, or theme CSS

Escalate to a custom block only when at least one of these is true:

- the layout depends on repeated structured data that core blocks cannot model cleanly
- the interaction is essential and cannot be handled by core blocks plus light enhancement
- the section must be reused across templates with controlled fields and editorial constraints

## Anti-Patterns

Avoid these mistakes when converting Make source to Gutenberg:

- copying Tailwind classes verbatim into block markup without extracting tokens
- treating every React component as a custom Gutenberg block
- preserving fake app state that should really be editor content or WP navigation
- hardcoding remote production image URLs in patterns when media should live in WordPress
- assuming every installed dependency in `package.json` is architecturally relevant

## Minimal MCP Review Checklist

Before generating Gutenberg code from a Make app, confirm:

- `package.json` identifies the real implementation stack
- `App.tsx` shows the page composition order
- global CSS files reveal reusable tokens
- representative sections show whether content is static, inline-array-based, or truly data-driven
- UI utility files reveal whether the app uses a reusable primitive system such as Radix/shadcn patterns
