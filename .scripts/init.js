let config = require('./config');
let replace = require('replace');
import('renamer').then(function (rnm) {
    const renamer = new rnm.default();

    // Replace namespaces
    replace({
        regex: 'EwStarter',
        replacement: config.namespace,
        paths: ['plugins/ewplugin', 'themes/ew-theme'],
        exclude: 'plugins/ewplugin/vendor, themes/ew-theme/vendor, themes/ew-theme/node_modules',
        recursive: true
    });

    // Replace @package name in plugin files
    replace({
        regex: 'EWPlugin',
        replacement: config.namespace,
        paths: ['plugins/ewplugin'],
        recursive: true
    });

    // Replace plugin function names
    replace({
        regex: '_ew_plugin',
        replacement: '_' + config.pluginNameForFunctions,
        paths: ['plugins/ewplugin'],
        recursive: true
    });

    // Replace base route
    replace({
        regex: 'wp-ew',
        replacement: config.baseRoute,
        paths: ['plugins/ewplugin', 'themes/ew-theme'],
        exclude: 'plugins/ewplugin/vendor, themes/ew-theme/vendor, themes/ew-theme/node_modules',
        recursive: true
    });

    // Replace theme name
    replace({
        regex: 'ew-theme',
        replacement: config.themeNameForFileNames,
        paths: ['.gitignore', 'azure-pipelines.yml', 'themes/ew-theme/theme-config.json', '.dockerignore', '.infra/config/supervisord.dev.conf', '.infra/docker-entrypoint.sh', 'Dockerfile']
    });

    // Replace artifact name in azure-pipelines
    replace({
        regex: 'ewStarter',
        replacement: config.artifactName,
        paths: ['azure-pipelines.yml']
    });

    // Replace plugin
    replace({
        regex: 'ewplugin',
        replacement: config.pluginNameForFileNames,
        paths: ['azure-pipelines.yml', '.gitignore', '.dockerignore', '.infra/docker-entrypoint.sh', 'Dockerfile']
    });

    // Replace plugin name in main/class-plugin.php
    replace({
        regex: 'ew-plugin',
        replacement: config.pluginNameForFileNames,
        paths: ['plugins/ewplugin/main/class-plugin.php']
    });

    // Replace plugin name in main/class-di-container.php
    replace({
        regex: 'ewstarter',
        replacement: config.pluginNameForFileNames,
        paths: ['plugins/htz-plugin/main/class-di-container.php']
    });

    // Replace domain
    replace({
        regex: 'wp-starter.ew.local',
        replacement: config.webAppServerDomain,
        paths: ['themes/ew-theme/theme-config.json', 'themes/ew-theme/package.json', 'Dockerfile']
    });

    // Rename plugin in folder and file names
    renamer.rename({
        files: ['plugins/**'],
        find: 'ewplugin',
        replace: config.pluginNameForFileNames,
        recursive: true,
    });

    // Rename theme folder
    renamer.rename({
        files: ['themes/*'],
        find: 'ew-theme',
        replace: config.themeNameForFileNames
    });
});
