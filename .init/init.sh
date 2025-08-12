#!/usr/bin/env bash
set -euo pipefail

# Locate the folder of this script (.init)
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"

CONFIG_FILE="$SCRIPT_DIR/config.conf"

if [[ ! -f "$CONFIG_FILE" ]]; then
    echo "❌ Config not found: $CONFIG_FILE"
    exit 1
fi

# Load config
# shellcheck disable=SC1090
. "$CONFIG_FILE"

# --- Helper for replacements ---
replace_text() {
    local search="$1"
    local replacement="$2"
    shift 2
    local files=("$@")

    for path in "${files[@]}"; do
        if [[ -d "$PROJECT_ROOT/$path" ]]; then
            find "$PROJECT_ROOT/$path" -type f -exec sed -i'' -e "s|$search|$replacement|g" {} +
        elif [[ -f "$PROJECT_ROOT/$path" ]]; then
            sed -i'' -e "s|$search|$replacement|g" "$PROJECT_ROOT/$path"
        fi
    done
}

# --- REPLACEMENTS ---
replace_text "EwStarter" "$namespace" plugins/ewplugin themes/ew-theme
replace_text "EWPlugin" "$namespace" plugins/ewplugin
replace_text "_ew_plugin" "_${pluginNameForFunctions}" plugins/ewplugin
replace_text "wp-ew" "$baseRoute" plugins/ewplugin themes/ew-theme
replace_text "ew-theme" "$themeNameForFileNames" \
    .gitignore azure-pipelines.yml themes/ew-theme/theme-config.json \
    .dockerignore .infra/config/supervisord.dev.conf \
    .infra/docker-entrypoint.sh Dockerfile
replace_text "ewplugin" "$pluginNameForFileNames" \
    azure-pipelines.yml .gitignore .dockerignore \
    .infra/docker-entrypoint.sh Dockerfile
replace_text "ew-plugin" "$pluginNameForFileNames" plugins/ewplugin/main/class-plugin.php
replace_text "ewstarter" "$pluginNameForFileNames" plugins/htz-plugin/main/class-di-container.php
replace_text "wp-starter.ew.local" "$webAppServerDomain" \
    themes/ew-theme/theme-config.json themes/ew-theme/package.json Dockerfile

# --- RENAMES ---
echo "📂 Renaming plugin folders/files"
find "$PROJECT_ROOT/plugins" -depth -name "*ewplugin*" -exec bash -c '
for f; do
    newf="${f//ewplugin/'"$pluginNameForFileNames"'}"
    if [[ "$f" != "$newf" ]]; then
        mv "$f" "$newf"
    fi
done
' _ {} +

echo "📂 Renaming theme folder"
find "$PROJECT_ROOT/themes" -depth -name "ew-theme" -exec bash -c '
for f; do
    newf="${f//ew-theme/'"$themeNameForFileNames"'}"
    if [[ "$f" != "$newf" ]]; then
        mv "$f" "$newf"
    fi
done
' _ {} +

echo "✅ Replacement & rename completed."

# --- CLEANUP ---
cd "$PROJECT_ROOT" # move out of .init before deleting
(
    sleep 1
    rm -rf "$SCRIPT_DIR"
) &

echo "✅ Init complete. .init removed."
