#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_ROOT="$(cd "${SCRIPT_DIR}/../../../.." && pwd)"
FIGMA_CONFIG="${THEME_ROOT}/figma.json"

if [[ ! -f "${FIGMA_CONFIG}" ]]; then
  echo "Error: missing figma.json at ${FIGMA_CONFIG}" >&2
  exit 1
fi

FIELD="app_url"
PAGE_KEY=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --field)
      FIELD="${2:-}"
      shift 2
      ;;
    --page-key)
      PAGE_KEY="${2:-}"
      shift 2
      ;;
    *)
      echo "Error: unknown argument '$1'" >&2
      exit 1
      ;;
  esac
done

python3 - "${FIGMA_CONFIG}" "${FIELD}" "${PAGE_KEY}" <<'PY'
import json
import re
import sys
from urllib.parse import urlparse
from pathlib import Path

config_path = Path(sys.argv[1])
field = sys.argv[2]
page_key = sys.argv[3]

ALLOWED_FIELDS = {"app_url", "figma_url", "site_url"}

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

pages = figma.get("pages")
if pages is not None and not isinstance(pages, dict):
    print("Error: figma.pages must be an object when provided", file=sys.stderr)
    raise SystemExit(1)

def is_valid_http_url(value: str) -> bool:
    try:
        parsed = urlparse(value)
    except ValueError:
        return False

    return parsed.scheme in {"http", "https"} and bool(parsed.netloc)

if isinstance(pages, dict):
    for mapping_key, mapping in pages.items():
        if not isinstance(mapping, dict):
            print(f"Error: figma.pages.{mapping_key} must be an object", file=sys.stderr)
            raise SystemExit(1)

        figma_url = mapping.get("figma_url")
        site_url = mapping.get("site_url")

        if not isinstance(figma_url, str) or not figma_url.strip():
            print(f"Error: figma.pages.{mapping_key}.figma_url must be a non-empty string", file=sys.stderr)
            raise SystemExit(1)

        if not isinstance(site_url, str) or not site_url.strip():
            print(f"Error: figma.pages.{mapping_key}.site_url must be a non-empty string", file=sys.stderr)
            raise SystemExit(1)

        if not is_valid_http_url(figma_url.strip()):
            print(f"Error: figma.pages.{mapping_key}.figma_url must be a valid HTTP/HTTPS URL", file=sys.stderr)
            raise SystemExit(1)

        if not is_valid_http_url(site_url.strip()):
            print(f"Error: figma.pages.{mapping_key}.site_url must be a valid HTTP/HTTPS URL", file=sys.stderr)
            raise SystemExit(1)

if field not in ALLOWED_FIELDS:
    print("Error: --field must be one of app_url, figma_url, site_url", file=sys.stderr)
    raise SystemExit(1)

if field == "app_url":
    print(app_url)
    raise SystemExit(0)

if not page_key:
    print("Error: --page-key is required when requesting figma_url or site_url", file=sys.stderr)
    raise SystemExit(1)

if not isinstance(pages, dict) or page_key not in pages:
    print(f"Error: figma.pages.{page_key} was not found", file=sys.stderr)
    raise SystemExit(1)

value = pages[page_key].get(field)
if not isinstance(value, str) or not value.strip():
    print(f"Error: figma.pages.{page_key}.{field} must be a non-empty string", file=sys.stderr)
    raise SystemExit(1)

print(value.strip())
PY
