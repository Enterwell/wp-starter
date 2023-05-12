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

    // Replace theme name
    replace({
        regex: 'ew-theme',
        replacement: config.themeNameForFileNames,
        paths: ['.gitignore', 'azure-pipelines-build.yml', 'azure-pipelines-qa.yml', '.ansible/vars', 'wp-content/themes/ew-theme/theme-config.json']
    });

    // Replace project name
    replace({
        regex: 'wp-starter',
        replacement: config.themeNameForFileNames,
        paths: ['.ansible/vars']
    });

    // Replace artifact name in azure-pipelines
    replace({
        regex: 'ewStarter',
        replacement: config.artifactName,
        paths: ['azure-pipelines-build.yml', 'azure-pipelines-qa.yml']
    });

    // Replace plugin
    replace({
        regex: 'ewplugin',
        replacement: config.pluginNameForFileNames,
        paths: ['azure-pipelines-build.yml', 'azure-pipelines-qa.yml', '.gitignore', '.ansible/vars']
    });

    // Replace plugin name in includes/class-plugin.php
    replace({
        regex: 'ew-plugin',
        replacement: config.pluginNameForFileNames,
        paths: ['wp-content/plugins/ewplugin/includes/class-plugin.php']
    });

    // Replace domain
    replace({
        regex: 'starter.local',
        replacement: config.webAppServerDomain,
        paths: ['wp-content/themes/ew-theme/theme-config.json', 'wp-content/themes/ew-theme/package.json', '.ansible/vars']
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
