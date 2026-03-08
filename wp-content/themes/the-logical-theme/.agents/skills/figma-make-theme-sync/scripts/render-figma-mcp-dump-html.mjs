import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const themeRoot = path.resolve(__dirname, '../../../../');
const defaultDumpRoot = path.join(themeRoot, '.artifacts', 'figma-mcp-debug');

function parseArgs(argv) {
  const args = {};

  for (let index = 2; index < argv.length; index += 1) {
    const token = argv[index];

    if (!token.startsWith('--')) {
      continue;
    }

    const key = token.slice(2);
    const next = argv[index + 1];

    if (!next || next.startsWith('--')) {
      args[key] = true;
      continue;
    }

    args[key] = next;
    index += 1;
  }

  return args;
}

function escapeHtml(value) {
  return String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');
}

const args = parseArgs(process.argv);
const dumpId = args['dump-id'] ?? 'ugHr0Gh5yVbWe2mtIPCLIG';
const dumpDir = path.join(defaultDumpRoot, dumpId);
const manifestPath = path.join(dumpDir, 'manifest.json');

if (!fs.existsSync(manifestPath)) {
  console.error(`Missing manifest: ${manifestPath}`);
  process.exit(1);
}

const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));

const sourceSections = (manifest.savedSources ?? []).map((relPath) => {
  const absPath = path.join(dumpDir, relPath);
  const content = fs.readFileSync(absPath, 'utf8');

  return `
    <details class="card">
      <summary>${escapeHtml(relPath)}</summary>
      <div class="meta">
        <a href="${escapeHtml(relPath)}">${escapeHtml(relPath)}</a>
      </div>
      <pre><code>${escapeHtml(content)}</code></pre>
    </details>
  `;
}).join('\n');

const imageSections = (manifest.savedImages ?? []).map((relPath) => {
  const absPath = path.join(dumpDir, relPath);
  const content = fs.readFileSync(absPath, 'utf8');

  return `
    <details class="card">
      <summary>${escapeHtml(relPath)}</summary>
      <div class="meta">
        <a href="${escapeHtml(relPath)}">${escapeHtml(relPath)}</a>
      </div>
      <pre><code>${escapeHtml(content)}</code></pre>
    </details>
  `;
}).join('\n');

const notes = (manifest.notes ?? [])
  .map((note) => `<li>${escapeHtml(note)}</li>`)
  .join('\n');

const html = `<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Figma MCP Dump ${escapeHtml(dumpId)}</title>
    <style>
      :root {
        --bg: #0b1020;
        --panel: #121931;
        --panel-2: #0f1529;
        --text: #eef2ff;
        --muted: #a8b1d1;
        --accent: #7dd3fc;
        --border: #273152;
      }

      * {
        box-sizing: border-box;
      }

      body {
        margin: 0;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        background: linear-gradient(180deg, #0a0f1e 0%, #11162a 100%);
        color: var(--text);
      }

      main {
        width: min(1200px, calc(100vw - 32px));
        margin: 0 auto;
        padding: 32px 0 56px;
      }

      h1, h2 {
        margin: 0 0 12px;
      }

      h1 {
        font-size: 28px;
      }

      h2 {
        font-size: 20px;
        margin-top: 28px;
      }

      p, li {
        color: var(--muted);
        line-height: 1.5;
      }

      a {
        color: var(--accent);
      }

      .hero, .card {
        background: rgba(18, 25, 49, 0.92);
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: 0 18px 50px rgba(0, 0, 0, 0.28);
      }

      .hero {
        padding: 24px;
      }

      .grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 16px;
        margin-top: 16px;
      }

      .stat {
        background: var(--panel-2);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 16px;
      }

      .stat strong {
        display: block;
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 6px;
      }

      details {
        margin-top: 14px;
        overflow: hidden;
      }

      summary {
        cursor: pointer;
        padding: 16px 18px;
        font-weight: 700;
      }

      .meta {
        padding: 0 18px 12px;
        color: var(--muted);
        font-size: 14px;
      }

      pre {
        margin: 0;
        padding: 18px;
        border-top: 1px solid var(--border);
        background: #0b1120;
        overflow: auto;
        font-size: 13px;
        line-height: 1.45;
      }

      ul {
        margin: 10px 0 0;
        padding-left: 20px;
      }
    </style>
  </head>
  <body>
    <main>
      <section class="hero">
        <h1>Figma MCP Dump</h1>
        <p>This page renders a repo-local snapshot of what the agent received from Figma MCP for the Make app.</p>
        <div class="grid">
          <div class="stat">
            <strong>Dump ID</strong>
            <span>${escapeHtml(dumpId)}</span>
          </div>
          <div class="stat">
            <strong>App URL</strong>
            <a href="${escapeHtml(manifest.appUrl)}">${escapeHtml(manifest.appUrl)}</a>
          </div>
          <div class="stat">
            <strong>Tool</strong>
            <span>${escapeHtml(manifest.tool ?? 'unknown')}</span>
          </div>
          <div class="stat">
            <strong>Generated By</strong>
            <span>${escapeHtml(manifest.generatedBy ?? 'unknown')}</span>
          </div>
        </div>
        <h2>Notes</h2>
        <ul>${notes}</ul>
      </section>

      <h2>Source Files</h2>
      ${sourceSections}

      <h2>Image Resources</h2>
      <p>These are MCP image-resource references. They include the original MCP URI and a blob prefix proving a binary payload existed agent-side.</p>
      ${imageSections}
    </main>
  </body>
</html>
`;

const outputPath = path.join(dumpDir, 'index.html');
fs.writeFileSync(outputPath, html, 'utf8');
console.log(`Wrote ${outputPath}`);
