// Imports
const gulp = require('gulp');
const pump = require('pump');
const sass = require('gulp-sass');
const sync = require('browser-sync');
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const rename = require('gulp-rename');
const uglifycss = require('gulp-uglifycss');
const concat = require('gulp-concat');
const del = require('del');
const CONFIG = require('./config');

// Config
const PATHS = CONFIG.PATHS;

// Constants
const NAME_ENTRY = 'main';
const NAME_CONCAT = 'styles.min.css';
const PATH_ENTRY = `${PATHS.styles}/${NAME_ENTRY}.scss`;
const PATH_OUTPUT = `${PATHS.dist}/${NAME_ENTRY}.min.css`;
const PATH_WEBPACK_STYLES = `${PATHS.dist}/bundle.min.css`;

/**
 * Builds the styles for the dev.
 */
gulp.task('styles-build-dev', (callback) => {
  pump([
    // Starts with main scss file
    gulp.src(PATH_ENTRY),

    // Starts SASS
    sass({
      includePaths: [
        // Paths for url resolving
        PATHS.assets,
      ]
    }),

    // Saves the file
    gulp.dest(PATHS.dist),

    // Reloads the browser sync
    sync.stream()
  ],
  callback);
});

gulp.task('styles-watch-dev', ['styles-build-dev'], () => (
  // Watches the changes on scss files
  gulp.watch(`${PATHS.assets}/**/*.scss`, ['styles-build-dev'])
));

/**
 * Builds the gulp styles for the production.
 */
gulp.task('styles-build-gulp-prod', ['build-clear-prod-before'], (callback) => {
  pump([
    // Starts with main scss file
    gulp.src(PATH_ENTRY),

    // Compiles the SASS
    sass({
      outputStyle: 'compressed',
      includePaths: [
        // Paths for url resolving
        PATHS.root + '/node_modules',
        PATHS.assets
      ]
    }),

    // PostCSS
    postcss([
      autoprefixer()
    ]),

    // Renames the file
    rename({
      suffix: '.min'
    }),

    // Uglify CSS
    uglifycss(),

    // Specifies the destination
    gulp.dest(PATHS.dist)
  ],
  callback);
});

/**
 * Builds the styles for the prod
 */
gulp.task('styles-concat-prod', ['js-build-prod', 'styles-build-gulp-prod'], (callback) => {
  pump([
    // Takes the built css file
    gulp.src([
      PATH_OUTPUT,
      PATH_WEBPACK_STYLES
    ]),

    // Concats the webpack bundle
    concat(NAME_CONCAT),

    // Exports to destination
    gulp.dest(PATHS.dist)
  ],
  callback);
});

/**
 * Styles clear prod
 */
gulp.task('styles-clear-prod', ['styles-concat-prod'], () => (
  // Deletes the files
  del([
    PATH_OUTPUT,
    PATH_WEBPACK_STYLES
  ])
));
