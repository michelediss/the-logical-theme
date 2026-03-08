# figma-make-theme-sync

Skill locale per `the-logical-theme` che genera codice Gutenberg partendo da `figma.json` con sorgente Figma Make e valida il risultato con screenshot responsive.

## Cosa fa

- legge `figma.app_url` da `figma.json`
- può risolvere rapporti persistenti tra pagine Figma Make e pagine del sito da `figma.pages`
- recupera contesto Figma Make tramite MCP
- genera o aggiorna `theme.json`, pattern, template parts e template
- per task di pagina, produce almeno un `templates/*.html`
- cattura screenshot input/output sui breakpoint Tailwind
- confronta i risultati e supporta un loop di correzione fino a 3 iterazioni

## Input attesi

- `task`: richiesta in linguaggio naturale
- `target_type`: `theme.json`, `pattern`, `part`, `template`
- `target_name`: slug del target, obbligatorio per i template
- `page_key`: chiave logica opzionale per usare un mapping definito in `figma.json`
- `output_path`: path esplicito opzionale
- `preview_url`: URL WordPress opzionale per la cattura output, con priorità più alta del mapping configurato

## Configurazione

La skill usa solo `figma.json` nella root del tema:

```json
{
  "figma": {
    "source": "make",
    "app_url": "https://www.figma.com/make/APP_ID",
    "pages": {
      "home": {
        "label": "Home page",
        "target_name": "front-page",
        "figma_url": "https://www.figma.com/make/APP_ID?screen=home",
        "site_url": "http://thelogicaltheme.localhost/"
      }
    }
  }
}
```

La priorità di risoluzione è:

1. `preview_url`
2. `figma.pages.<page_key>.site_url`
3. errore esplicito se non esiste un URL sito risolvibile

Per Figma screenshot:

1. `--figma-url` esplicito
2. `figma.pages.<page_key>.figma_url`
3. `figma.app_url`

## Visual QA

Breakpoint usati:

- `sm`: 640
- `md`: 768
- `lg`: 1024
- `xl`: 1280
- `2xl`: 1536

Gli screenshot sono full-page con altezza viewport `1600`.

Artefatti:

- `.artifacts/visual-qa/<target-name>/<run-id>/input/`
- `.artifacts/visual-qa/<target-name>/<run-id>/output/iter-N/`
- `.artifacts/visual-qa/<target-name>/<run-id>/diff/iter-N/`
- `.artifacts/visual-qa/<target-name>/<run-id>/reports/`

Il confronto è LLM-guidato e valuta almeno:

- `layout_spacing`
- `content_hierarchy`
- `typography_scale`
- `media_crop_or_size`
- `cta_navigation_placement`

## Script inclusi

- `scripts/get-figma-app-url.sh`
- `scripts/capture-figma-make-screenshots.mjs`
- `scripts/capture-wp-screenshots.mjs`
- `scripts/prepare-visual-qa-report.mjs`

Script npm nel tema:

```bash
npm run visual-qa:figma -- --page-key home --target-name front-page
npm run visual-qa:wp -- --page-key home --target-name front-page --iteration 1
npm run visual-qa:report -- --target-name page --iteration 1
./.agents/skills/figma-make-theme-sync/scripts/get-figma-app-url.sh --field site_url --page-key home
```

## Workflow sintetico

1. Leggere struttura del tema e convenzioni locali.
2. Validare `figma.json`.
3. Recuperare contesto Figma Make via MCP.
4. Catturare screenshot di riferimento.
5. Generare o aggiornare il target richiesto.
6. Catturare screenshot WordPress.
7. Produrre report confronto.
8. Correggere e ripetere fino a esito soddisfacente o massimo 3 iterazioni.

## Note

- La skill non usa node id persistiti in repo.
- Per task orientati a pagine non può dichiarare successo senza report finale di visual QA.
- Per eseguire gli script browser serve Playwright installato nel tema.
- Se esiste `page_key`, la skill usa i mapping configurati in `figma.json` per Figma screenshot e site preview.
