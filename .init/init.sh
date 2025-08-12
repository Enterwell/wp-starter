#!/usr/bin/env bash
set -euo pipefail

CONFIG_FILE="./config.conf"

if [[ ! -f "$CONFIG_FILE" ]]; then
    echo "❌ Config file '$CONFIG_FILE' not found!"
    exit 1
fi

# Load config
# shellcheck disable=SC1090
. "$CONFIG_FILE"

# ---- Function for text replacement ----
replace_text() {
    local search="$1"
    local replacement="$2"
    shift 2
    local files=("$@")

    for path in "${files[@]}"; do
        if [[ -d "$path" ]]; then
            find "$path" -type f -exec sed -i'' -e "s|$search|$replacement|g" {} +
        elif [[ -f "$path" ]]; then
            sed -i'' -e "s|$search|$replacement|g" "$path"
        fi
    done
}

# ---- TEXT REPLACEMENTS ----
# 1. EwStarter → namespace
replace_text "EwStarter" "$namespace" plugins/ewplugin themes/ew-theme

# 2. EWPlugin → namespace
replace_text "EWPlugin" "$namespace" plugins/ewplugin

# 3. _ew_plugin → _pluginNameForFunctions
replace_text "_ew_plugin" "_${pluginNameForFunctions}" plugins/ewplugin

# 4. wp-ew → baseRoute
replace_text "wp-ew" "$baseRoute" plugins/ewplugin themes/ew-theme

# 5. ew-theme → themeNameForFileNames
replace_text "ew-theme" "$themeNameForFileNames" \
    .gitignore azure-pipelines.yml themes/ew-theme/theme-config.json \
    .dockerignore .infra/config/supervisord.dev.conf \
    .infra/docker-entrypoint.sh Dockerfile

# 6. ewplugin → pluginNameForFileNames
replace_text "ewplugin" "$pluginNameForFileNames" \
    azure-pipelines.yml .gitignore .dockerignore \
    .infra/docker-entrypoint.sh Dockerfile

# 7. ew-plugin → pluginNameForFileNames
replace_text "ew-plugin" "$pluginNameForFileNames" \
    plugins/ewplugin/main/class-plugin.php

# 8. ewstarter → pluginNameForFileNames
replace_text "ewstarter" "$pluginNameForFileNames" \
    plugins/htz-plugin/main/class-di-container.php

# 9. wp-starter.ew.local → webAppServerDomain
replace_text "wp-starter.ew.local" "$webAppServerDomain" \
    themes/ew-theme/theme-config.json themes/ew-theme/package.json Dockerfile

# ---- FILE/FOLDER RENAMES ----
echo "📂 Renaming plugin folders/files"
find plugins -depth -name "*ewplugin*" -exec bash -c '
for f; do
    newf="${f//ewplugin/'"$pluginNameForFileNames"'}"
    if [[ "$f" != "$newf" ]]; then
        mv "$f" "$newf"
    fi
done
' _ {} +

echo "📂 Renaming theme folder"
find themes -depth -name "ew-theme" -exec bash -c '
for f; do
    newf="${f//ew-theme/'"$themeNameForFileNames"'}"
    if [[ "$f" != "$newf" ]]; then
        mv "$f" "$newf"
    fi
done
' _ {} +

echo "✅ Replacement & rename completed."

# Save the folder path before changing directory
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

# Go up one level so we’re no longer inside .init
cd "$SCRIPT_DIR/.."

# Delete the .init folder after the script exits
(
  sleep 1
  rm -rf "$SCRIPT_DIR"
) &
