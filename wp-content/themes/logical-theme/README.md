# The Logical Theme

The Logical Theme is a custom WordPress theme built for modular layout composition, design-system driven styling, and plugin-based feature extension.

## Features

- Template hierarchy remapped to `templates/`
- Component-based PHP structure (`components/`, `template-parts/`, `partials/`)
- Built-in support for the Logical Design System workflow
- Optional Bootstrap Icons integration (theme option)
- Minified JS build flow with admin trigger

## Requirements

- WordPress 6.x
- PHP 8.0+

## Installation

1. Copy the theme to:
   - `wp-content/themes/logical-theme`
2. Activate **The Logical Theme** from WordPress admin.
3. (Optional) Activate companion plugins from this monorepo.

## Theme Structure

- `assets/`: CSS, JS, SCSS, icon assets
- `components/`: reusable PHP components
- `template-parts/`: page sections and partial templates
- `templates/`: full template files used by remapped hierarchy
- `partials/`: utility/theme behavior modules
- `settings/`: theme admin settings pages and helpers
- `tools/`: local build/runtime tools (e.g. Dart Sass binary)

## Development Notes

- The theme loads templates from `templates/` via `functions.php`.
- Child overrides should go in `wp-content/themes/logical-theme-child/`.
- SCSS input for design-system customization is managed through:
  - plugin defaults in `logical-design-system`
  - optional child-theme overrides in `logical-theme-child/assets/scss/lds-input.json`
- Compiled CSS target files are:
  - `assets/css/lds-style.css`
  - `assets/css/lds-style.min.css`

## JavaScript Build Behavior

- If `assets/js/main.min.js` exists, it is enqueued.
- Otherwise, `assets/js/main.js` is loaded and library/partial files are localized.
- Admin bar includes a **Minify JS** action to regenerate `main.min.js`.

## Releases

Releases are automated by GitHub Actions (`.github/workflows/release.yml`):

- Trigger on push to `master` (or manually via `workflow_dispatch`)
- Package theme zip
- Publish GitHub Release with versioned tag

## License

GPL-2.0-or-later
