# The Logical Theme Summary

`the-logical-theme` is a block-first WordPress theme with a small PHP bootstrap layer and a Vite-based front-end pipeline.

## Architecture

- `functions.php` loads the theme bootstrap and hooks setup, front-end assets, editor assets, and custom blocks into WordPress.
- `inc/assets.php` switches between the local Vite dev server and the production manifest in `assets/.vite/manifest.json`.
- `inc/blocks.php` discovers and registers custom theme blocks from `blocks/*/block.json`.
- `inc/patterns.php` registers the custom block pattern category, while WordPress auto-discovers native pattern files from `patterns/`.
- `templates/` and `parts/` contain the block theme HTML templates used by the Site Editor.
- `parts/cookie-banner.html` mounts the plugin-owned `simple-cookie-consent/banner` block so cookie consent layout is controlled from the Site Editor instead of plugin PHP hooks.
- `src/js/` and `src/css/` contain the authored source files; built output is written to `assets/`.
- `theme.json` defines the source-of-truth design tokens and editorial defaults that Gutenberg must know natively, while Tailwind consumes those same tokens as a bridge instead of defining a second design system.

## Naming Conventions

- PHP functions use the explicit `the_logical_theme_` prefix and snake_case, following WordPress naming conventions.
- Asset handles use the `the-logical-theme-` prefix to remain unique in the global WordPress enqueue namespace.
- Theme text-domain values remain `the-logical-theme`, matching the slug defined in `style.css`.
- JavaScript uses descriptive camelCase names, with one module per concern.
- Custom blocks use slug format `custom/<name>` and live in `blocks/<name>/`.

## Important Files

- `functions.php`: theme bootstrap and core hooks.
- `inc/assets.php`: Vite integration and asset registration.
- `inc/blocks.php`: custom block discovery and registration.
- `inc/patterns.php`: custom block pattern category registration.
- `cf7-forms/`: versioned Contact Form 7 JSON manifests owned by the theme and synced with the local `cf7-sync` WP-CLI plugin.
- `theme.json`: design system and editor configuration.
- `partials/block-availability.php`: block availability bootstrap that loads the runtime whitelist logic and the related Appearance admin UI for block categories.
- `partials/theme-options.php`: Settings admin screen for theme-owned runtime options such as frontend jQuery disable, comments disable, and image upload restrictions.
- `partials/privacy-controller-data.php`: Settings admin screen for the global `privacy_controller_data` option and the `[privacy key="..."]` shortcode used in policy pages.
- `.agents/skills/figma-make-theme-sync/`: repository-local skill, runtime resolver scripts, visual QA tooling, Lighthouse performance audit tooling, and run-manifest based resume support for Figma Make to Gutenberg workflows.
- `tailwind.config.js`: token bridge that maps WordPress CSS variables into Tailwind utilities for theme-authored CSS and markup.
- `src/js/app.js`: front-end bootstrap entrypoint.
- `src/js/blocks/editor.js`: shared editor entry for custom blocks.
- `src/js/blocks/view.js`: shared front-end entry for custom block behavior.
- `docs/allowed-blocks.md`: AI-facing guide to allowed blocks and their intended use.
- `docs/custom-blocks.md`: development rules for future custom blocks.
- `style.css`: theme registration metadata required by WordPress.
- `vite.config.js` and `tailwind.config.js`: build pipeline configuration.

## JavaScript

- `src/js/app.js` is the front-end bootstrap entrypoint. It waits for `DOMContentLoaded` and then initializes the small progressive-enhancement modules used by the theme.
- `src/js/modules/` contains one module per concern:
  - `fade-in.js` reveals eligible front-end sections on scroll with GSAP and `ScrollTrigger`. It only targets managed elements, skips nodes marked with `.no-fadein`, and shows content immediately when the user prefers reduced motion. Use `.no-fadein` consistently on sections that can appear above the fold so primary content does not load in a hidden state.
  - `menu.js` keeps the theme-level `.menu-is-open` state on `<html>` synchronized with the core Navigation block responsive overlay, so scroll locking reflects the real open or closed state of the mobile menu.
  - `page-transitions.js` handles subtle page entry animation and intercepts eligible same-origin links to run a short exit transition before navigation. External links, modified clicks, downloads, admin routes, and same-page hash jumps are ignored.
  - `simple-cookie-consent-banner.js` owns frontend motion for the plugin-rendered cookie banner, including GSAP entry/exit and settings-panel reveal coordination.
- `src/js/blocks/` contains editor and front-end scripts for custom blocks. Shared entrypoints (`editor.js` and `view.js`) import block-specific modules so Vite can bundle them into the assets consumed by WordPress.
- `src/js/patterns/` contains lightweight hooks for pattern-level DOM behavior. These hooks should stay optional and should not become a second application bootstrap layer.
- Keep JavaScript as progressive enhancement. Theme templates, patterns, and custom blocks must remain usable without client-side JS.
- Theme-authored frontend presentation for the plugin cookie banner lives in `src/css/blocks/simple-cookie-consent-banner.css`, while the plugin remains responsible for markup and consent logic.

## Vite Asset Flow

- In development, `inc/assets.php` checks whether the local Vite dev server is available and, if so, enqueues source entries such as `src/js/app.js` directly from `http://localhost:5173`.
- In production, Vite builds the source files into `assets/` and writes `assets/.vite/manifest.json`. WordPress resolves the final bundle filenames from that manifest before enqueueing them.
- The main front-end entrypoint is `src/js/app.js`. Additional entries exist for block editor scripts, block view scripts, SEO editor utilities, and CSS bundles as defined in `vite.config.js`.
- When JavaScript or CSS files under `src/` change, run `npm run build` so the production bundles in `assets/` stay aligned with the source.

## Design System Contract

- `theme.json` is the source of truth for stable global tokens and Gutenberg-facing defaults such as palette, typography presets, spacing scale, layout widths, and shared radius values.
- `tailwind.config.js` must consume WordPress CSS variables from `theme.json`; it must not become a parallel token registry with divergent colors, spacing values, or widths.
- Keep `settings.layout` in `theme.json` so the editor, front-end styles, and AI generation flows share the same `contentSize` and `wideSize` baseline.
- Avoid using `theme.json` to impose broad layout behavior on neutral containers. Section spacing and composition should usually live in patterns, templates, block supports, or scoped CSS instead of global `core/group` padding.
- Keep `styles.elements` minimal and global. Component-specific variants should live in block styles, patterns, or custom block CSS rather than being forced into every core element default.

## Maintenance Notes

- When source files in `src/` change, rebuild assets with `npm run build` so production bundles in `assets/` stay aligned.
- Keep all user-facing theme strings translatable with the `the-logical-theme` text domain. Load translations from `languages/`, use WordPress i18n helpers in PHP, and call `wp_set_script_translations()` for any theme-owned JS handle that uses `wp.i18n`.
- Keep theme-owned admin pages under a single clear ownership boundary. `Settings -> Theme Options` is reserved for global runtime toggles owned by the theme, while more specific feature pages can still live under `Settings` or `Appearance` when their scope is narrower.
- Keep the privacy content source centralized in `Settings -> Privacy Data`, backed by the single `privacy_controller_data` option and consumed in content through `[privacy key="..."]`.
- Do not leave user-facing copy hardcoded inside `templates/*.html` or `parts/*.html`. In block themes those files are not reliable extraction targets, so translatable copy should live in PHP-registered patterns referenced by the HTML templates.
- After adding or changing strings, regenerate catalogs with `npm run i18n:build` so `languages/the-logical-theme.pot`, locale `.po/.mo`, and JS translation JSON files stay aligned.
- Keep new PHP APIs prefixed with `the_logical_theme_` to avoid collisions with plugins or other themes.
- Prefer block patterns and `theme.json` settings over custom PHP rendering unless the editor cannot express the requirement cleanly.
- When updating tokens or layout defaults, change `theme.json` first and let Tailwind keep consuming the generated CSS variables rather than redefining the values in `tailwind.config.js`.
- Keep Contact Form 7 manifests under `wp-content/themes/the-logical-theme/cf7-forms` so they are versioned with the theme; the local `cf7-sync` WP-CLI command reads from that path by default.
- When adding a new custom block, update both `docs/custom-blocks.md` and `docs/allowed-blocks.md` if the block should be available to AI-assisted template generation.
- The `figma-make-theme-sync` skill must read `docs/` during development, but runtime facts for blocks, tokens, visual QA, and performance audit thresholds must come from theme code and its resolver scripts.
