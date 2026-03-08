---
name: theme-docs-context
description: "Use this skill when a task depends on the canonical Markdown documentation of `the-logical-theme`. It loads and relies on every file in `docs/`: `docs/theme-overview.md`, `docs/allowed-blocks.md`, `docs/custom-blocks.md`, and `docs/skill-sync.md`."
---

# Theme Docs Context

Use this skill when the task must be grounded in the canonical theme documentation stored in `docs/`.

This skill's only specialization is that it must include, use, and know all Markdown files in `docs/`.

## Required files

Always read all of these files before acting:

- `docs/theme-overview.md`
- `docs/allowed-blocks.md`
- `docs/custom-blocks.md`
- `docs/skill-sync.md`

Do not treat one of them as optional. This skill is only being used correctly when all four files are part of the working context.

When a task also touches `wp-content/plugins/cf7-sync`, additionally read:

- `wp-content/plugins/cf7-sync/README.md`

## Usage rules

- Treat `docs/theme-overview.md` as the architectural overview and maintenance guide.
- Treat `docs/allowed-blocks.md` as the AI-facing guide to allowed blocks and their intended usage.
- Treat `docs/custom-blocks.md` as the implementation standard for new custom blocks.
- Treat `docs/skill-sync.md` as the operational guide for keeping repository-local skills aligned with the global Codex skills directory.
- Treat `wp-content/plugins/cf7-sync/README.md` as the local operational reference for the CF7 sync plugin when the task involves that plugin.
- When documentation and code appear inconsistent, call out the mismatch explicitly instead of silently choosing one.
- When changing theme behavior or conventions, update the affected file or files in `docs/` so the documentation remains canonical.

## When this skill applies

Use it for tasks such as:

- documenting or reviewing theme conventions
- creating or updating custom blocks according to theme rules
- validating whether a proposed template, pattern, or block uses allowed blocks
- updating AI skills or workflows that depend on theme documentation
- updating or validating repository-local Codex skill sync behavior
- documenting or changing the local `cf7-sync` plugin together with theme-adjacent workflows
- checking whether code changes remain aligned with documented architecture

Do not use this skill for generic WordPress work that does not depend on the theme documentation in `docs/`.
