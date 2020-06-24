// Imports
const gulp = require('gulp');
const del = require('del');
const CONFIG = require('./config');

// PATHS
const PATHS = CONFIG.PATHS;

/**
 * Clears the dist before build
 */
gulp.task('build-clear-prod-before', () => (
  // Deletes the all files in dist
  del([
    `${PATHS.dist}/*`
  ])
));

/**
 * Builds the production
 */
gulp.task('build-prod', [
  'build-clear-prod-before',
  'js-build-prod',
  'styles-build-gulp-prod',
  'styles-concat-prod',
  'styles-clear-prod'
]);
