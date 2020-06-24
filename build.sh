#!/usr/bin/env bash

if [[ ! -d .build ]]; then
  mkdir .build
fi

# Clear build dir
rm -rf .build/*

echo "[1/5] => .build dir cleared"
echo "[2/5] => WordPress copy [STARTED]"

cp -r wp-admin .build/wp-admin
cp -r wp-includes .build/wp-includes
cp *.php .build
rm ./.build/wp-config.php
rm ./.build/deploy.php

echo "[2/5] => WordPress copy [FINISHED]"
echo "[3/5] => theme build [STARTED]"
echo -e "........................................\n"

# Build theme
cd wp-content/themes/ew-theme
yarn
yarn build
cd ../../../
echo "[3/5] => theme build [FINISHED]"
echo -e "........................................\n"

mkdir -p ./.build/wp-content/

echo "[4/5] => themes/plugins copy [STARTED]"
tar --exclude node_modules -czf ./.build/wp-content/themes.tar.gz wp-content/themes
tar --exclude node_modules -czf ./.build/wp-content/plugins.tar.gz wp-content/plugins

tar -xzf ./.build/wp-content/themes.tar.gz -C ./.build/
tar -xzf ./.build/wp-content/plugins.tar.gz -C ./.build/

rm ./.build/wp-content/themes.tar.gz
rm ./.build/wp-content/plugins.tar.gz
echo "[4/5] => themes/plugins copy [FINISHED]"

echo "[5/5] => files clear [STARTED]"

# Remove plugin files
rm -rf ./.build/wp-content/plugins/ewplugin/tests
rm -rf ./.build/wp-content/plugins/ewplugin/.phpcs.xml.dist
rm -rf ./.build/wp-content/plugins/ewplugin/.travis.yml
rm -rf ./.build/wp-content/plugins/ewplugin/phpunit.phar
rm -rf ./.build/wp-content/plugins/ewplugin/phpunit.xml.dist
rm -rf ./.build/wp-content/plugins/ewplugin/composer.json
rm -rf ./.build/wp-content/plugins/ewplugin/composer.lock

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

# Create uploads folder and copy .htaccess to it
mkdir ./.build/wp-content/uploads
cp ./wp-content/uploads/.htaccess ./.build/wp-content/uploads/
echo "[5/5] => files clear [FINISHED]"
echo -e "........................................\n"
