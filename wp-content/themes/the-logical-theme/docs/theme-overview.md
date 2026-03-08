# The Logical Theme Summary

`the-logical-theme` is a block-first WordPress theme with a small PHP bootstrap layer and a Vite-based front-end pipeline.

## Architecture

- `functions.php` loads the theme bootstrap and hooks setup, front-end assets, editor assets, and custom blocks into WordPress.
- `inc/assets.php` switches between the local Vite dev server and the production manifest in `assets/.vite/manifest.json`.
- `inc/blocks.php` discovers and registers custom theme blocks from `blocks/*/block.json`.
- `inc/patterns.php` registers the custom block pattern category and loads all PHP pattern definitions from `patterns/`.
- `templates/` and `parts/` contain the block theme HTML templates used by the Site Editor.
- `src/js/` and `src/css/` contain the authored source files; built output is written to `assets/`.
- `theme.json` defines global design tokens, spacing, typography, block settings, and template-part metadata.

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
- `inc/patterns.php`: block pattern registration loader.
- `theme.json`: design system and editor configuration.
- `src/js/app.js`: front-end bootstrap entrypoint.
- `src/js/blocks/editor.js`: shared editor entry for custom blocks.
- `src/js/blocks/view.js`: shared front-end entry for custom block behavior.
- `docs/allowed-blocks.md`: AI-facing guide to allowed blocks and their intended use.
- `docs/custom-blocks.md`: development rules for future custom blocks.
- `style.css`: theme registration metadata required by WordPress.
- `vite.config.js` and `tailwind.config.js`: build pipeline configuration.

## Maintenance Notes

- When source files in `src/` change, rebuild assets with `npm run build` so production bundles in `assets/` stay aligned.
- Keep new PHP APIs prefixed with `the_logical_theme_` to avoid collisions with plugins or other themes.
- Prefer block patterns and `theme.json` settings over custom PHP rendering unless the editor cannot express the requirement cleanly.
- When adding a new custom block, update both `docs/custom-blocks.md` and `docs/allowed-blocks.md` if the block should be available to AI-assisted template generation.
