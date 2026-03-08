#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
DEFAULT_SOURCE_ROOT="${THEME_ROOT}/.agents/skills"
DEFAULT_TARGET_ROOT="${HOME}/.codex/skills"

SKILL_NAME=""
SOURCE_ROOT="${DEFAULT_SOURCE_ROOT}"
TARGET_ROOT="${DEFAULT_TARGET_ROOT}"
LIST_ONLY="false"

usage() {
  cat <<EOF
Usage: $(basename "$0") --skill <skill-name> [options]

Sync a repository-local skill to the global Codex skills directory.

Required:
  --skill <name>           Skill directory name to sync

Options:
  --source-root <path>     Source skills root
                           Default: ${DEFAULT_SOURCE_ROOT}
  --target-root <path>     Target Codex skills root
                           Default: ${DEFAULT_TARGET_ROOT}
  --list                   List available source skills and exit
  -h, --help               Show this help

Examples:
  $(basename "$0") --list
  $(basename "$0") --skill figma-make-theme-sync
  $(basename "$0") --skill theme-docs-context
  $(basename "$0") --skill all
  $(basename "$0") --skill my-skill --source-root /path/to/.agents/skills
EOF
}

list_skills() {
  if [[ ! -d "${SOURCE_ROOT}" ]]; then
    echo "Error: source root not found: ${SOURCE_ROOT}" >&2
    exit 1
  fi

  find "${SOURCE_ROOT}" -mindepth 1 -maxdepth 1 -type d -printf '%f\n' | sort
}

sync_skill() {
  local skill_name="$1"
  local source_dir="${SOURCE_ROOT}/${skill_name}"
  local target_dir="${TARGET_ROOT}/${skill_name}"

  if [[ ! -d "${source_dir}" ]]; then
    echo "Error: source skill not found: ${source_dir}" >&2
    exit 1
  fi

  if [[ ! -f "${source_dir}/SKILL.md" ]]; then
    echo "Error: missing SKILL.md in ${source_dir}" >&2
    exit 1
  fi

  if command -v rsync >/dev/null 2>&1; then
    rsync -a --delete "${source_dir}/" "${target_dir}/"
  else
    rm -rf "${target_dir}"
    mkdir -p "${target_dir}"
    cp -R "${source_dir}/." "${target_dir}/"
  fi

  echo "Synced skill '${skill_name}'"
  echo "Source dir: ${source_dir}"
  echo "Target dir: ${target_dir}"
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --skill)
      SKILL_NAME="${2:-}"
      shift 2
      ;;
    --source-root)
      SOURCE_ROOT="${2:-}"
      shift 2
      ;;
    --target-root)
      TARGET_ROOT="${2:-}"
      shift 2
      ;;
    --list)
      LIST_ONLY="true"
      shift
      ;;
    --help|-h)
      usage
      exit 0
      ;;
    *)
      echo "Error: unknown argument '$1'" >&2
      usage >&2
      exit 1
      ;;
  esac
done

if [[ "${LIST_ONLY}" == "true" ]]; then
  list_skills
  exit 0
fi

if [[ -z "${SKILL_NAME}" ]]; then
  echo "Error: --skill is required" >&2
  usage >&2
  exit 1
fi

mkdir -p "${TARGET_ROOT}"

echo "Source root: ${SOURCE_ROOT}"
echo "Target root: ${TARGET_ROOT}"

if [[ "${SKILL_NAME}" == "all" ]]; then
  while IFS= read -r skill; do
    sync_skill "${skill}"
  done < <(list_skills)
  exit 0
fi

sync_skill "${SKILL_NAME}"
