// Imports
const gulp = require('gulp');
const webpack = require('webpack');
const webpackConfigProd = require('../webpack/webpack.production');

/**
 * Builds with webpack.
 */
gulp.task('js-build-prod', ['build-clear-prod-before'], (callback) => {
  // Sets the env variable
  process.env.NODE_ENV = 'production';

  // Defines the webpack
  const bundler = webpack(webpackConfigProd);

  // Starts the webpack
  bundler.run((error, stats) => {
    // Defines the stats string
    let statsString = 'No stats needed';

    // Checks for error
    if (error || stats.hasErrors()) {
      // Loggs the error
      console.log('---------');
      console.error(error);

      // Sets the stats string to verbose
      statsString = stats.toJson('verbose');
    }

    // Prints the stats
    console.log('---------');
    console.log(statsString);

    // Logs done
    console.log('---------');
    console.log('Done!');

    // Calls the callback
    callback(error);
  });
});
