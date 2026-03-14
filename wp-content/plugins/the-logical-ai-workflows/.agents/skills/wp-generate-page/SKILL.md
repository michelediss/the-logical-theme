---
name: wp-generate-page
description: "Generate one WordPress page from ai-source inputs prepared from Figma Make, respecting whitelisted blocks and theme constraints."
---

# wp-generate-page

Use this skill only when `ai-source/<page>/` already contains prepared deterministic inputs.

## Required inputs

- `ai-source/<page>/manifest.json`
- `ai-source/<page>/code/figma-raw/`
- `ai-source/<page>/screen-figma/`
- `wp-content/themes/the-logical-theme/docs/block/whitelisted-blocks.md`
- `references/figma-make-architecture.md`

Before reasoning, validate the page inputs with `scripts/validate-inputs.sh`.

## What this skill does

- interprets Figma Make artifacts as layout intent, not as code to copy
- selects only allowed blocks from the whitelist
- generates one draft implementation for one page
- writes a report that explains block choices, degradations, and unresolved constraints

## What this skill must not do

- do not fetch `figma.json`
- do not call MCP
- do not create screenshots
- do not work on multiple pages in one reasoning pass
- do not introduce blocks that are not explicitly allowed
- do not produce a silent best guess if key data is missing

## Required reading order

1. `wp-content/themes/the-logical-theme/docs/block/whitelisted-blocks.md`
2. `references/figma-make-architecture.md`
3. `ai-source/<page>/manifest.json`
4. files inside `ai-source/<page>/code/figma-raw/`
5. screenshots inside `ai-source/<page>/screen-figma/`

## Output contract

Write or update:

- `ai-source/<page>/code/wp-draft/`
- `ai-source/<page>/reports/generate-page.md`

The report must include:

- recognized page sections
- block choices tied to the whitelist
- differences between Figma intent and WordPress output
- assumptions made
- blockers requiring manual intervention

## Internal reference

`references/figma-make-architecture.md` is an internal interpretation guide for Figma Make inputs. It is not shared theme documentation and must not be treated as the source of truth for available blocks.
