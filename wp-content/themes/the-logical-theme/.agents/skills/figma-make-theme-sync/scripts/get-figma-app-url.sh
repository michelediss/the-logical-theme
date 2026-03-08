#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_ROOT="$(cd "${SCRIPT_DIR}/../../../.." && pwd)"
FIGMA_CONFIG="${THEME_ROOT}/figma.json"

if [[ ! -f "${FIGMA_CONFIG}" ]]; then
  echo "Error: missing figma.json at ${FIGMA_CONFIG}" >&2
  exit 1
fi

python3 - "${FIGMA_CONFIG}" <<'PY'
import json
import re
import sys
from pathlib import Path

config_path = Path(sys.argv[1])

try:
    raw = config_path.read_text(encoding="utf-8")
except OSError as exc:
    print(f"Error: unable to read {config_path}: {exc}", file=sys.stderr)
    raise SystemExit(1)

try:
    data = json.loads(raw)
except json.JSONDecodeError as exc:
    print(f"Error: invalid JSON in {config_path}: {exc}", file=sys.stderr)
    raise SystemExit(1)

figma = data.get("figma")
if not isinstance(figma, dict):
    print("Error: figma.json must contain a top-level 'figma' object", file=sys.stderr)
    raise SystemExit(1)

source = figma.get("source")
if source != "make":
    print("Error: figma.source must be 'make'", file=sys.stderr)
    raise SystemExit(1)

app_url = figma.get("app_url")
if not isinstance(app_url, str) or not app_url.strip():
    print("Error: figma.app_url must be a non-empty string", file=sys.stderr)
    raise SystemExit(1)

app_url = app_url.strip()

if not re.match(r"^https://www\.figma\.com/make/[^/\s?#]+(?:[?#].*)?$", app_url):
    print(
        "Error: figma.app_url must match https://www.figma.com/make/APP_ID",
        file=sys.stderr,
    )
    raise SystemExit(1)

print(app_url)
PY
