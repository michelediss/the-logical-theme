# wp-review-page

Repository-local Codex skill for one-pass visual and structural review.

Use it after:

1. `wp-generate-page` has produced `code/wp-draft`
2. `scripts/figma-screenshots.mjs --mode wp --variant draft` has produced local screenshots

The skill compares intent and structure, not just pixels. It must never enter an automatic multi-pass loop.
