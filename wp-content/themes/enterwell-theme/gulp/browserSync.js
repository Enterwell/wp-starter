// Imports
const gulp = require('gulp');
const browserSync = require('browser-sync');
const webpackSettings = require('../webpack/webpack.settings');

// Settings
const webAppServerSettings = webpackSettings.WebAppServerSettings;

/**
 * Gulp browser sync task.
 */
gulp.task('browser-sync', () => {
  // Starts the browser sync
  browserSync.init({
    // Browser sync is proxy to our app server
    proxy: webAppServerSettings.address,
    port: webpackSettings.BrowserSyncPort,

    // Injects changes
    injectChanges: true,

    // Files
    files: [
      'assets/dist/main.css',
      '**/*.php',
      '**/*.twig'
    ]
  });
});
