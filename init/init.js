let config = require('./config');
let replace = require('replace');
const Renamer = require('renamer');

const renamer = new Renamer();

// Replace namespaces
replace({
  regex: 'EwStarter',
  replacement: config.namespace,
  paths: ['wp-content/plugins/enterwell-plugin', 'wp-content/themes/enterwell-theme'],
  exclude: 'wp-content/plugins/enterwell-plugin/vendor, wp-content/themes/enterwell-theme/vendor, wp-content/themes/enterwell-theme/node_modules',
  recursive: true
});

// Replace class names
replace({
  regex: 'EnterwellPlugin',
  replacement: config.pluginNameForClassNames,
  paths: ['wp-content/plugins/enterwell-plugin'],
  recursive: true
});

// Replace file names
replace({
  regex: 'enterwell-plugin',
  replacement: config.pluginNameForFileNames,
  paths: ['wp-content/plugins/enterwell-plugin'],
  recursive: true,
});

// Replace function names
replace({
  regex: 'test_plugin',
  replacement: config.pluginNameForFunctions,
  paths: ['wp-content/plugins/enterwell-plugin'],
  recursive: true
});

// Replace abstract controller file name
replace({
  regex: 'class-aewstarter-controller.php',
  replacement: config.abstractControllerFileName,
  paths: ['wp-content/plugins/enterwell-plugin'],
  recursive: true
});

// Replace base route
replace({
  regex: 'wp-ew',
  replacement: config.baseRoute,
  paths: ['wp-content/plugins/enterwell-plugin', 'wp-content/themes/enterwell-theme'],
  exclude: 'wp-content/plugins/enterwell-plugin/vendor, wp-content/themes/enterwell-theme/vendor, wp-content/themes/enterwell-theme/node_modules',
  recursive: true
});

// Replace theme name in .gitignore
replace({
  regex: 'test-theme',
  replacement: config.themeNameForFileNames,
  paths: ['.gitignore']
});

// Rename abstract controller
renamer.rename({
  files: ['wp-content/plugins/enterwell-plugin/**'],
  find: 'class-aewstarter-controller.php',
  replace: config.abstractControllerFileName,
  recursive: true,
});

// Rename plugin in folder and file names
renamer.rename({
  files: ['wp-content/plugins/**'],
  find: 'enterwell-plugin',
  replace: config.pluginNameForFileNames,
  recursive: true,
});

// Rename theme folder
renamer.rename({
  files: ['wp-content/themes/**'],
  find: 'enterwell-theme',
  replace: config.themeNameForFileNames
});
