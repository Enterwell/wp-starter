var config = require('./config');
var replace = require('replace');
const Renamer = require('renamer')

const renamer = new Renamer()

// Replace
replace({
  regex: config.replaceSearch,
  replacement: config.replaceValue,
  paths: ['wp-content'],
  recursive: true
});

// Rename
renamer.rename({
  files: [ 'wp-content/*' ],
  find: config.renameSearch,
  replace: config.renameValue
});
