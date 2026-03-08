# Figma MCP Debug Dump

This directory contains a concrete dump captured by the agent from the Figma MCP server for the Make app in `figma.json`.

Important limitation:

- Figma MCP is available to the agent, not to plain Node or PHP scripts in the repo.
- Because of that, the repo-local scripts here inspect and validate an already-saved dump instead of fetching MCP data themselves.
- This dump is diagnostic-only and is not part of the production Make-to-Gutenberg workflow.

What is saved here:

- a manifest with the app URL and MCP-specific notes
- selected source files returned by `get_design_context`
- image resource references returned by MCP, including `uri`, `mimeType`, and a `blob` prefix for verification

Useful commands:

```bash
npm run skill:inspect-mcp-debug
npm run skill:test-mcp-debug
```
