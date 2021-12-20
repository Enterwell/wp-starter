let config = require('./config');
let replace = require('replace');
import('renamer').then(function (rnm) {
    const renamer = new rnm.default();

    // Replace namespaces
    replace({
        regex: 'EWStarter',
        replacement: config.namespace,
        paths: ['wp-content/plugins/ewplugin', 'wp-content/themes/ew-theme'],
        exclude: 'wp-content/plugins/ewplugin/vendor, wp-content/themes/ew-theme/vendor, wp-content/themes/ew-theme/node_modules',
        recursive: true
    });

    // Replace class names
    replace({
        regex: 'EWPlugin',
        replacement: config.pluginNameForClassNames,
        paths: ['wp-content/plugins/ewplugin'],
        recursive: true

    });

    // Replace file names
    replace({
        regex: '-ewplugin',
        replacement: '-' + config.pluginNameForFileNames,
        paths: ['wp-content/plugins/ewplugin'],
        recursive: true,
    });

    // Replace function names
    replace({
        regex: '_ewplugin',
        replacement: '_' + config.pluginNameForFunctions,
        paths: ['wp-content/plugins/ewplugin'],
        recursive: true
    });

    // Replace abstract controller file name
    replace({
        regex: 'class-aewplugin-controller.php',
        replacement: config.abstractControllerFileName,
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

    // Replace theme name in .gitignore
    replace({
        regex: 'ew-theme',
        replacement: config.themeNameForFileNames,
        paths: ['.gitignore', 'azure-pipelines.yml']
    });

    // Replace theme name in .gitignore
    replace({
        regex: 'ewplugin',
        replacement: config.pluginNameForFileNames,
        paths: ['azure-pipelines.yml', 'wp-content/plugins/ewplugin/tests/bootstrap.php']
    });

    // Replace webAppServerAddress in theme-config.json
    replace({
        regex: 'http:   //ew-wp-starter.local/',
        replacement: config.webAppServerAddress,
        paths: ['wp-content/themes/ew-theme/theme-config.json']
    });

    // Rename abstract controller
    renamer.rename({
        files: ['wp-content/plugins/ewplugin/**'],
        find: 'class-aewplugin-controller.php',
        replace: config.abstractControllerFileName,
        recursive: true,
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
