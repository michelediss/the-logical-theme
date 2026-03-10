# wp-generate-page

Repository-local Codex skill for page generation from prepared `ai-source/<page>/` inputs.

Use this skill only after deterministic prep has completed:

1. `scripts/init-manifest.mjs`
2. `scripts/ingest-figma.mjs`
3. `scripts/figma-screenshots.mjs --mode figma`

The skill is intentionally narrow:

- reads one page at a time
- reasons about section intent and block composition
- writes draft output under `ai-source/<page>/code/wp-draft`
- writes a rationale report under `ai-source/<page>/reports/generate-page.md`

Do not use it for download, screenshot capture, or global batch orchestration.
