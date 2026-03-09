# Codex Skill Sync

This document explains how to sync a repository-local skill to the global Codex and Kimi skills directories.

## Purpose

Some Codex environments discover available skills from `~/.codex/skills` or `~/.kimi/skills` instead of using only the repository copy.

This theme provides a sync utility so the repository version of a skill can be treated as the source of truth and then copied into the global skills directories when needed.

## Script

The sync script is:

```text
scripts/sync-codex-skill.sh
```

## What It Does

- copies one skill directory from a source skills root
- writes it into one or more target skills roots
- removes files in the target that no longer exist in the source when `rsync` is available
- validates that the source skill exists and contains `SKILL.md`

## Default Paths

- source root: `.agents/skills`
- target roots: `~/.codex/skills`, `~/.kimi/skills`

Because these are defaults, the script works out of the box for theme-local skills and keeps the Codex and Kimi copies aligned in one run.

## Usage

List available local skills:

```bash
wp-content/themes/the-logical-theme/scripts/sync-codex-skill.sh --list
```

Sync a specific skill:

```bash
wp-content/themes/the-logical-theme/scripts/sync-codex-skill.sh --skill figma-make-theme-sync
```

Sync another skill:

```bash
wp-content/themes/the-logical-theme/scripts/sync-codex-skill.sh --skill theme-docs-context
```

Sync all local skills:

```bash
wp-content/themes/the-logical-theme/scripts/sync-codex-skill.sh --skill all
```

Use custom roots:

```bash
wp-content/themes/the-logical-theme/scripts/sync-codex-skill.sh \
  --skill my-skill \
  --source-root /path/to/.agents/skills \
  --target-root /path/to/.codex/skills
```

## Rules

- The repository copy should be treated as the source of truth.
- Run the sync script after changing a repository-local skill that also needs to exist in `~/.codex/skills` or `~/.kimi/skills`.
- If a skill is not visible in Codex or Kimi but exists in the repository, check whether the global copy is missing or outdated.
- The script syncs one skill at a time on purpose, so changes remain explicit and reviewable.
- `--skill all` is available when you intentionally want to sync every local skill in one run.
- When `figma-make-theme-sync` changes, sync the whole skill directory so `SKILL.md`, `README.md`, `skill.yaml`, and `scripts/` stay aligned across repository-local and global copies.
