let config = require('./config');
let replace = require('replace');
const Renamer = require('renamer');

const renamer = new Renamer();

// Replace namespaces
replace({
  regex: 'EwStarter',
  replacement: config.namespace,
  paths: ['plugins/enterwell-plugin'],
  exclude: 'plugins/enterwell-plugin/vendor',
  recursive: true
});

// Replace class names
replace({
  regex: 'EnterwellPlugin',
  replacement: config.pluginNameForClassNames,
  paths: ['plugins/enterwell-plugin'],
  recursive: true
});

// Replace file names
replace({
  regex: 'enterwell-plugin',
  replacement: config.pluginNameForFileNames,
  paths: ['plugins/enterwell-plugin'],
  recursive: true,
});

// Replace function names
replace({
  regex: 'test_plugin',
  replacement: config.pluginNameForFunctions,
  paths: ['plugins/enterwell-plugin'],
  recursive: true
});

// Replace abstract controller file name
replace({
  regex: 'class-aewstarter-controller.php',
  replacement: config.abstractControllerFileName,
  paths: ['plugins/enterwell-plugin'],
  recursive: true
});

// Replace base route
replace({
  regex: 'wp-ew',
  replacement: config.baseRoute,
  paths: ['plugins/enterwell-plugin'],
  recursive: true
});

// Rename plugin in folder and file names
renamer.rename({
  files: ['plugins/**'],
  find: 'enterwell-plugin',
  replace: config.pluginNameForFileNames,
  recursive: true,
});

// Rename abstract controller
renamer.rename({
  files: ['plugins/enterwell-plugin/**'],
  find: 'class-aewstarter-controller.php',
  replace: config.abstractControllerFileName,
  recursive: true,
});

