#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_SCRIPT="${SCRIPT_DIR}/../../../plugins/the-logical-ai-workflows/scripts/sync-codex-skill.sh"

if [[ ! -x "${PLUGIN_SCRIPT}" ]]; then
  echo "Error: plugin sync script not found or not executable: ${PLUGIN_SCRIPT}" >&2
  exit 1
fi

exec "${PLUGIN_SCRIPT}" "$@"
