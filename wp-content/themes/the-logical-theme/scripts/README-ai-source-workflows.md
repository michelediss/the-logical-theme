# AI Source Workflow Scripts

Questa cartella contiene gli script deterministici del workflow WordPress basato su input Figma Make.

Le skill AI vivono in `.agents/skills/` e intervengono solo dopo che questi script hanno preparato i dati in `ai-source/`.

Il download degli artifact Figma Make passa tramite un boundary esterno:

- `codex exec`
- server MCP Figma configurato in Codex

Gli script di questa cartella non parlano direttamente il protocollo MCP. Usano Codex CLI come wrapper non interattivo per rilevare e leggere le risorse esposte dal file Make.

## Script disponibili

### `init-manifest.mjs`

Scopo:

- inizializza la struttura minima di `ai-source/<page>/`
- crea o aggiorna `manifest.json`
- aggiorna `ai-source/index.json`

Uso:

```bash
node scripts/init-manifest.mjs --page-id home
node scripts/init-manifest.mjs --all
node scripts/init-manifest.mjs --all --figma-config /percorso/personalizzato/figma.json
```

Input richiesti:

- `figma.json` del tema
- `--page-id <id>` oppure `--all`

Output:

- `ai-source/index.json`
- `ai-source/<page>/manifest.json`
- directory standard della pagina

Note:

- non scarica codice
- non genera screenshot
- può essere rieseguito in modo sicuro per riallineare stato e cartelle

### `ingest-figma.mjs`

Scopo:

- prepara gli artifact raw per una pagina
- scarica il codice e gli asset utili dal file Figma Make via MCP
- salva i metadata di origine Figma in `code/figma-raw/meta/`
- aggiorna stato e storico nel manifest

Uso:

```bash
node scripts/ingest-figma.mjs --page-id home
node scripts/ingest-figma.mjs --all
node scripts/ingest-figma.mjs --all --fail-fast
node scripts/ingest-figma.mjs --page-id home --batch-size 8
node scripts/ingest-figma.mjs --page-id home --limit 10
```

Input richiesti:

- `figma.json`
- `--page-id <id>` oppure `--all`

Output:

- `ai-source/<page>/code/figma-raw/meta/source-page.json`
- `ai-source/<page>/code/figma-raw/meta/resource-index.json`
- `ai-source/<page>/code/figma-raw/source/...`
- `ai-source/<page>/code/figma-raw/assets/images/...`

Note:

- richiede `codex` disponibile nel PATH
- richiede server MCP Figma configurato e autenticato in Codex
- richiede accesso rete verso MCP Figma quando il comando viene eseguito
- se una pagina fallisce, per default le altre proseguono; `--fail-fast` interrompe subito il batch
- `--batch-size` controlla quante risorse MCP vengono lette per sessione Codex
- `--limit` serve solo per debug o validazione parziale del file Make

### `figma-screenshots.mjs`

Scopo:

- genera screenshot Figma e/o screenshot WordPress locali
- salva un indice run in `reports/screenshot-index.json`

Uso:

```bash
node scripts/figma-screenshots.mjs --page-id home --mode figma
node scripts/figma-screenshots.mjs --page-id home --mode wp --variant draft
node scripts/figma-screenshots.mjs --all --mode both --variant reviewed
```

Input richiesti:

- `figma.json`
- `--page-id <id>` oppure `--all`

Input opzionali:

- `--mode figma|wp|both`
- `--variant draft|reviewed|optimized`

Output:

- `ai-source/<page>/screen-figma/desktop.png`
- `ai-source/<page>/screen-figma/mobile.png`
- `ai-source/<page>/screen-wp/<variant>-desktop.png`
- `ai-source/<page>/screen-wp/<variant>-mobile.png`

Note:

- usa Playwright
- per `--mode figma` serve un `figma_url` valido
- per `--mode wp` serve un `site_url` locale valido e raggiungibile

### `ai-ingest-figma.mjs`

Scopo:

- orchestra la preparazione dati completa per una o più pagine
- esegue in sequenza init manifest, ingest e screenshot Figma

Uso:

```bash
node scripts/ai-ingest-figma.mjs --page-id home
node scripts/ai-ingest-figma.mjs --all
node scripts/ai-ingest-figma.mjs --all --fail-fast
```

Ordine interno:

1. `init-manifest.mjs`
2. `ingest-figma.mjs`
3. `figma-screenshots.mjs --mode figma`

Note:

- lavora sempre una pagina alla volta anche quando usi `--all`
- se l’ingest MCP o la cattura screenshot Figma falliscono, il manifest della pagina viene marcato in errore

### `sync-codex-skill.sh`

Scopo:

- sincronizza una skill repository-local da `.agents/skills/`
- copia la skill in `~/.codex/skills`
- copia la skill in `~/.kimi/skills`

Uso:

```bash
bash scripts/sync-codex-skill.sh --list
bash scripts/sync-codex-skill.sh --skill wp-generate-page
bash scripts/sync-codex-skill.sh --skill wp-review-page
bash scripts/sync-codex-skill.sh --skill wp-optimize-lighthouse
bash scripts/sync-codex-skill.sh --skill all
```

Note:

- `.agents/skills/` è la source of truth
- le directory globali sono copie runtime
- lo script verifica almeno la presenza di `SKILL.md`
- se `rsync` è disponibile, usa sync speculare della cartella skill

## Script di supporto interni

### `lib/ai-source-utils.mjs`

Questo file non è un entrypoint operativo.

Serve come libreria condivisa per:

- parsing CLI
- risoluzione percorsi
- lettura/scrittura JSON
- creazione struttura `ai-source/`
- gestione `manifest.json` e `index.json`

Non eseguirlo direttamente.

## Flusso consigliato

### Preparazione minima di una pagina

```bash
node scripts/init-manifest.mjs --page-id home
node scripts/ingest-figma.mjs --page-id home
node scripts/figma-screenshots.mjs --page-id home --mode figma
```

### Preparazione orchestrata

```bash
node scripts/ai-ingest-figma.mjs --page-id home
```

### Sync skill dopo modifica repository-local

```bash
bash scripts/sync-codex-skill.sh --skill wp-generate-page
bash scripts/sync-codex-skill.sh --skill wp-review-page
bash scripts/sync-codex-skill.sh --skill wp-optimize-lighthouse
```

## Guardrail operativi

- non usare questi script per prendere decisioni AI sul mapping Figma Make → WordPress
- non trattare `figma-make-architecture.md` come documentazione condivisa del tema
- non scrivere output AI direttamente fuori da `ai-source/`
- non usare batch impliciti: `--all` deve restare un ciclo esplicito pagina-per-pagina
- non aggiungere in `package.json` script che puntino a `.agents/skills/*`
