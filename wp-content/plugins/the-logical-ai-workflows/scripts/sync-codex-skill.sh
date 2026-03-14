#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
WORKSPACE_ROOT="$(cd "${PLUGIN_ROOT}/../../.." && pwd)"
PLUGIN_SOURCE_ROOT="${PLUGIN_ROOT}/.agents/skills"
THEME_SOURCE_ROOT="${WORKSPACE_ROOT}/wp-content/themes/the-logical-theme/.agents/skills"
DEFAULT_TARGET_ROOT="${HOME}/.codex/skills"
DEFAULT_KIMI_TARGET_ROOT="${HOME}/.kimi/skills"

SKILL_NAME=""
SOURCE_ROOT=""
TARGET_ROOTS=("${DEFAULT_TARGET_ROOT}" "${DEFAULT_KIMI_TARGET_ROOT}")
CUSTOM_SOURCE_ROOT="false"
CUSTOM_TARGET_ROOTS="false"
LIST_ONLY="false"

usage() {
  cat <<EOF
Usage: $(basename "$0") --skill <skill-name> [options]

Sync a repository-local skill to the global Codex and Kimi skills directories.

Required:
  --skill <name>           Skill directory name to sync

Options:
  --source-root <path>     Source skills root
                           Default: plugin skills root, except theme-docs-context from theme root
  --target-root <path>     Target skills root. Repeat to sync to multiple roots.
                           Default: ${DEFAULT_TARGET_ROOT}
                                    ${DEFAULT_KIMI_TARGET_ROOT}
  --list                   List available source skills and exit
  -h, --help               Show this help

Examples:
  $(basename "$0") --list
  $(basename "$0") --skill wp-generate-page
  $(basename "$0") --skill wp-review-page
  $(basename "$0") --skill wp-optimize-lighthouse
  $(basename "$0") --skill theme-docs-context
  $(basename "$0") --skill all
  $(basename "$0") --skill my-skill --source-root /path/to/.agents/skills
EOF
}

default_source_root_for_skill() {
  local skill_name="$1"

  if [[ "${skill_name}" == "theme-docs-context" ]]; then
    echo "${THEME_SOURCE_ROOT}"
    return
  fi

  echo "${PLUGIN_SOURCE_ROOT}"
}

skill_exists_in_root() {
  local root="$1"
  local skill_name="$2"
  [[ -d "${root}/${skill_name}" ]]
}

list_skills() {
  if [[ "${CUSTOM_SOURCE_ROOT}" == "true" ]]; then
    if [[ ! -d "${SOURCE_ROOT}" ]]; then
      echo "Error: source root not found: ${SOURCE_ROOT}" >&2
      exit 1
    fi

    find "${SOURCE_ROOT}" -mindepth 1 -maxdepth 1 -type d -printf '%f\n' | sort
    return
  fi

  {
    if [[ -d "${PLUGIN_SOURCE_ROOT}" ]]; then
      find "${PLUGIN_SOURCE_ROOT}" -mindepth 1 -maxdepth 1 -type d -printf '%f\n'
    fi

    if skill_exists_in_root "${THEME_SOURCE_ROOT}" "theme-docs-context"; then
      printf '%s\n' "theme-docs-context"
    fi
  } | sort -u
}

sync_skill() {
  local skill_name="$1"
  local resolved_source_root="${SOURCE_ROOT}"
  local source_dir
  local target_root
  local target_dir

  if [[ -z "${resolved_source_root}" ]]; then
    resolved_source_root="$(default_source_root_for_skill "${skill_name}")"
  fi

  source_dir="${resolved_source_root}/${skill_name}"

  if [[ ! -d "${source_dir}" ]]; then
    echo "Error: source skill not found: ${source_dir}" >&2
    exit 1
  fi

  if [[ ! -f "${source_dir}/SKILL.md" ]]; then
    echo "Error: missing SKILL.md in ${source_dir}" >&2
    exit 1
  fi

  if [[ ! -f "${source_dir}/skill.yaml" ]]; then
    echo "Warning: missing skill.yaml in ${source_dir}" >&2
  fi

  if [[ ! -f "${source_dir}/README.md" ]]; then
    echo "Warning: missing README.md in ${source_dir}" >&2
  fi

  echo "Synced skill '${skill_name}'"
  echo "Source dir: ${source_dir}"
  for target_root in "${TARGET_ROOTS[@]}"; do
    target_dir="${target_root}/${skill_name}"
    mkdir -p "${target_root}"

    if command -v rsync >/dev/null 2>&1; then
      rsync -a --delete "${source_dir}/" "${target_dir}/"
    else
      rm -rf "${target_dir}"
      mkdir -p "${target_dir}"
      cp -R "${source_dir}/." "${target_dir}/"
    fi

    echo "Target dir: ${target_dir}"
  done
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --skill)
      SKILL_NAME="${2:-}"
      shift 2
      ;;
    --source-root)
      SOURCE_ROOT="${2:-}"
      CUSTOM_SOURCE_ROOT="true"
      shift 2
      ;;
    --target-root)
      if [[ "${CUSTOM_TARGET_ROOTS}" == "false" ]]; then
        TARGET_ROOTS=()
        CUSTOM_TARGET_ROOTS="true"
      fi
      TARGET_ROOTS+=("${2:-}")
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

if [[ "${CUSTOM_SOURCE_ROOT}" == "false" ]] && [[ "${SKILL_NAME}" != "all" ]]; then
  SOURCE_ROOT="$(default_source_root_for_skill "${SKILL_NAME}")"
fi

if [[ "${CUSTOM_SOURCE_ROOT}" == "true" ]]; then
  echo "Source root: ${SOURCE_ROOT}"
else
  echo "Default plugin source root: ${PLUGIN_SOURCE_ROOT}"
  echo "Theme-only source root: ${THEME_SOURCE_ROOT}"
fi
printf 'Target roots:\n'
for target_root in "${TARGET_ROOTS[@]}"; do
  echo "  - ${target_root}"
done

if [[ "${SKILL_NAME}" == "all" ]]; then
  while IFS= read -r skill; do
    SOURCE_ROOT=""
    sync_skill "${skill}"
  done < <(list_skills)
  exit 0
fi

sync_skill "${SKILL_NAME}"
