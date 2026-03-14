#!/usr/bin/env bash

set -euo pipefail

PAGE_DIR="${1:-}"

if [[ -z "${PAGE_DIR}" ]]; then
  echo "Usage: $(basename "$0") <ai-source-page-dir>" >&2
  exit 1
fi

test -f "${PAGE_DIR}/manifest.json"
test -d "${PAGE_DIR}/code/wp-draft"
test -d "${PAGE_DIR}/screen-figma"
test -d "${PAGE_DIR}/screen-wp"

echo "Review input contract looks valid for ${PAGE_DIR}"
