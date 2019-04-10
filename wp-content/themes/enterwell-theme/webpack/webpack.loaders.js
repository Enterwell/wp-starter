const settings = require('./webpack.settings');
const PATHS = settings.PATHS;

/**
 * Set up images loader for webpack.
 * This loader loads images as base64 encoded url-s if they are smaller
 * than limit size. Otherwise it behaves like simple file loader.
 *
 * @link    https://github.com/webpack-contrib/file-loader
 * @link    https://github.com/webpack-contrib/url-loader
 *
 * @type {{test: RegExp, exclude: RegExp[], loader: string, options: {limit: number, outputPath: string}}}
 */
const ImagesLoader = {
  test: /\.(jpe?g|png|gif|svg)$/i,
  exclude: [/node_modules/, /vendors/],
  loader: 'url-loader',
  options: {
    limit: 1024,

    // Set up output path so images processed with this loader
    // are placed in images folder in webpack output dir.
    outputPath: 'images/'
  }
};

/**
 * Loads javascript using babel.
 * Babel configuration is set in .babelrc file that is in project's root folder.
 *
 * @type {{test: RegExp, exclude: RegExp[], loader: string}}
 */
const JavascriptLoader = {
  test: /\.js(x)?$/,
  exclude: [/node_modules/, /vendors/],
  loader: 'babel-loader'
};

module.exports = {
  ImagesLoader,
  JavascriptLoader
};