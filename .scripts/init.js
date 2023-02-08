let config = require('./config');
let replace = require('replace');
import('renamer').then(function (rnm) {
    const renamer = new rnm.default();

    // Replace namespaces
    replace({
        regex: 'EwStarter',
        replacement: config.namespace,
        paths: ['wp-content/plugins/ewplugin', 'wp-content/themes/ew-theme'],
        exclude: 'wp-content/plugins/ewplugin/vendor, wp-content/themes/ew-theme/vendor, wp-content/themes/ew-theme/node_modules',
        recursive: true
    });

    // Replace @package name in plugin files
    replace({
        regex: 'EWPlugin',
        replacement: config.namespace,
        paths: ['wp-content/plugins/ewplugin'],
        recursive: true
    });

    // Replace plugin function names
    replace({
        regex: '_ew_plugin',
        replacement: '_' + config.pluginNameForFunctions,
        paths: ['wp-content/plugins/ewplugin'],
        recursive: true
    });

    // Replace base route
    replace({
        regex: 'wp-ew',
        replacement: config.baseRoute,
        paths: ['wp-content/plugins/ewplugin', 'wp-content/themes/ew-theme'],
        exclude: 'wp-content/plugins/ewplugin/vendor, wp-content/themes/ew-theme/vendor, wp-content/themes/ew-theme/node_modules',
        recursive: true
    });

    // Replace theme name in .gitignore and azure-pipelines.yml
    replace({
        regex: 'ew-theme',
        replacement: config.themeNameForFileNames,
        paths: ['.gitignore', 'azure-pipelines.yml']
    });

    // Replace artifact name in azure-pipelines.yml
    replace({
        regex: 'ewStarter',
        replacement: config.artifactName,
        paths: ['azure-pipelines.yml']
    });

    // Replace plugin name in azure-pipelines.yml
    replace({
        regex: 'ewplugin',
        replacement: config.pluginNameForFileNames,
        paths: ['azure-pipelines.yml']
    });

    // Replace plugin name in includes/class-plugin.php
    replace({
        regex: 'ew-plugin',
        replacement: config.pluginNameForFileNames,
        paths: ['wp-content/plugins/ewplugin/includes/class-plugin.php']
    });

    // Replace webAppServerAddress in theme-config.json
    replace({
        regex: 'starter.local',
        replacement: config.webAppServerDomain,
        paths: ['wp-content/themes/ew-theme/theme-config.json']
    });

    // Replace domain in package.json
    replace({
        regex: 'starter.local',
        replacement: config.webAppServerDomain,
        paths: ['wp-content/themes/ew-theme/package.json']
    });

    replace({
        regex: 'ew-theme',
        replacement: config.themeNameForFileNames,
        paths: ['wp-content/themes/ew-theme/theme-config.json']
    });

    // Rename plugin in folder and file names
    renamer.rename({
        files: ['wp-content/plugins/**'],
        find: 'ewplugin',
        replace: config.pluginNameForFileNames,
        recursive: true,
    });

    // Rename theme folder
    renamer.rename({
        files: ['wp-content/themes/*'],
        find: 'ew-theme',
        replace: config.themeNameForFileNames
    });
});
