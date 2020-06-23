#!/usr/bin/env bash

if [ ! -d .build ]; then
  mkdir .build
fi

# Clear build dir
rm -rf .build/*

cp -r wp-admin .build/wp-admin
cp -r wp-includes .build/wp-includes
cp *.php .build
rm ./.build/wp-config.php
rm ./.build/deploy.php

# Build theme
cd wp-content/themes/ew-theme
yarn
yarn build
cd ../../../

mkdir -p ./.build/wp-content/

tar --exclude node_modules -czf ./.build/wp-content/themes.tar.gz wp-content/themes
tar --exclude node_modules -czf ./.build/wp-content/plugins.tar.gz wp-content/plugins

tar -xzf ./.build/wp-content/themes.tar.gz -C ./.build/
tar -xzf ./.build/wp-content/plugins.tar.gz -C ./.build/

rm ./.build/wp-content/themes.tar.gz
rm ./.build/wp-content/plugins.tar.gz

# Remove theme files
rm -rf ./.build/wp-content/themes/ew-theme/.scripts
rm -rf ./.build/wp-content/themes/ew-theme/gulp
rm -rf ./.build/wp-content/themes/ew-theme/webpack
rm -rf ./.build/wp-content/themes/ew-theme/assets/gutenberg
rm -rf ./.build/wp-content/themes/ew-theme/assets/js/components
rm -rf ./.build/wp-content/themes/ew-theme/assets/js/helpers
rm -rf ./.build/wp-content/themes/ew-theme/assets/js/pages
rm -rf ./.build/wp-content/themes/ew-theme/assets/js/react
rm -rf ./.build/wp-content/themes/ew-theme/assets/js/services
rm -rf ./.build/wp-content/themes/ew-theme/assets/js/main.js
rm -rf ./.build/wp-content/themes/ew-theme/assets/styles
rm -rf ./.build/wp-content/themes/ew-theme/.babelrc
rm -rf ./.build/wp-content/themes/ew-theme/.editorconfig
rm -rf ./.build/wp-content/themes/ew-theme/.eslintignore
rm -rf ./.build/wp-content/themes/ew-theme/.eslintrc
rm -rf ./.build/wp-content/themes/ew-theme/gulpfile.js
rm -rf ./.build/wp-content/themes/ew-theme/README.md
rm -rf ./.build/wp-content/themes/ew-theme/yarn.lock
rm -rf ./.build/wp-content/themes/ew-theme/composer.json
rm -rf ./.build/wp-content/themes/ew-theme/package.json
rm -rf ./.build/wp-content/themes/ew-theme/composer.lock
