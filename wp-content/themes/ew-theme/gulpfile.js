// Imports
const gulp = require('gulp');
require('./gulp/js');
require('./gulp/styles');
require('./gulp/browserSync');
require('./gulp/build');

/**
 * Default task.
 */
gulp.task('default', () => {
  // Echoes the wisdom
  console.log('---------');
  console.log('Well hello there...');
  console.log('To start development run "yarn start"');
  console.log('To build for production run "yarn build"');
  console.log('');
  console.log('Godspeed.');
  console.log('---------');
});
