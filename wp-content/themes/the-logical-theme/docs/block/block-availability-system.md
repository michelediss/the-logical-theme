# Block Availability System

This file documents how block availability is determined at runtime in `the-logical-theme`.

It is the conceptual reference for the availability system. The runtime source of truth remains the theme code and its machine-readable export.

## Runtime Sources Of Truth

- `partials/block-availability/runtime.php` defines curated categories, runtime catalog building, default enabled categories, and block availability normalization.
- `partials/block-availability.php` is the theme bootstrap entrypoint for the availability system.
- `partials/block-availability/utility/export-block-registry.php` exports the full registered block registry enriched with current allowlist state.
- `partials/block-availability/utility/export-whitelisted-blocks-md.php` generates a Markdown reference for the blocks currently in whitelist.
- `inc/blocks.php` discovers custom theme blocks from `blocks/*/block.json`.
- `.agents/skills/figma-make-theme-sync/scripts/export-theme-blocks.php` exports the current runtime catalog and normalized availability state from a bootstrapped WordPress environment.

For AI-assisted generation, the export script should be treated as the primary machine-readable source for which blocks are currently available.

## Categories

The runtime groups blocks into five categories:

- `core`: structural and content blocks that are always part of the theme baseline.
- `blog`: dynamic editorial and query-driven blocks.
- `woocommerce`: WooCommerce blocks discovered at runtime only when WooCommerce is active.
- `third_party`: blocks registered by plugins or other external code outside the `core`, `woocommerce`, and theme `custom` namespaces.
- `custom`: theme-owned custom blocks discovered from `blocks/*/block.json`.

These categories are managed independently. A block belongs to its runtime category and is not reassigned across categories in the admin UI.

## How The Runtime Catalog Is Built

The catalog is assembled dynamically:

- `core` starts from registered `core/*` blocks, then excludes the blocks classified into `blog`.
- `blog` starts from a curated baseline and adds dynamic registered core blocks where appropriate.
- `woocommerce` is populated from registered `woocommerce/*` blocks only when WooCommerce is active.
- `third_party` is populated from registered blocks whose namespace is not `core`, `woocommerce`, or `custom`.
- `custom` is populated from custom block metadata files discovered in `blocks/*/block.json`.

This means the runtime catalog can change based on:

- the active WordPress and Gutenberg environment
- plugin availability, especially WooCommerce
- newly added or removed custom theme blocks
- saved availability settings from the admin UI

## Whitelist And Blacklist Behavior

The system behaves like a category-aware allowlist with optional category-level enablement.

- `core` is the stable baseline and remains available as defined by runtime normalization.
- `blog` is enabled by default but can be disabled through saved settings.
- `woocommerce` is only relevant when the plugin is active.
- `third_party` is always visible when plugin blocks are registered and can be curated block by block in its own section.
- `custom` is derived from theme blocks and managed in its own category area.

Within each category, the admin UI can control which blocks remain allowed. A block may exist in the runtime catalog but not be currently allowed for insertion if the saved settings exclude it.

Because of this distinction, do not use a Markdown list as the authority for current availability.

When the Block Availability admin page is saved, the theme also regenerates:

- `docs/block/block-registry.json`
- `docs/block/whitelisted-blocks.md`

These derived files are convenience exports for human review and AI-assisted workflows. Runtime behavior still comes from the live PHP catalog and saved settings.

## What The Export Script Provides

`.agents/skills/figma-make-theme-sync/scripts/export-theme-blocks.php` returns:

- `curated_groups`: the category definitions and curated defaults
- `catalog`: the runtime-discovered block catalog grouped by category
- `settings`: the normalized saved settings
- `custom_blocks`: the discovered custom block names
- `custom_block_metadata`: metadata from each `blocks/*/block.json`
- `allowed_blocks`: the effective allowlist after runtime normalization

For AI or agent workflows, this export answers:

- which blocks exist right now
- which blocks are grouped under each category
- which blocks are effectively allowed right now

The utility export under `partials/block-availability/utility/` adds a second layer:

- full registered block metadata from `WP_Block_Type_Registry`
- current allowlist state for each registered block
- a derived Markdown reference containing only whitelisted blocks

## Documentation Boundary

This file explains the system.

It does not try to document every possible block in detail. Usage guidance belongs in `docs/block-composition-guide.md`.

## Maintenance Rules

- Update this file when the availability model, category logic, or admin behavior changes.
- Do not duplicate the full PHP runtime logic here line by line.
- When documentation and runtime disagree, treat runtime as authoritative and fix the docs.
