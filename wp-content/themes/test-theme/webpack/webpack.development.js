/* eslint comma-dangle: 0 */
// Imports
const webpack = require('webpack');

// Include settings
const settings = require('./webpack.settings');

const PATHS = settings.PATHS;
const WebAppServerSettings = settings.WebAppServerSettings;
const WebpackDevServerSettings = settings.WebpackDevServerSettings;

// Include plugins
const plugins = require('./webpack.plugins');

// Include resolve
const resolve = require('./webpack.resolve');

// Include common loaders
const loaders = require('./webpack.loaders');

const ImagesLoader = loaders.ImagesLoader;
const JavascriptLoader = loaders.JavascriptLoader;

/**
 * Loader that handles .scss files that do not use css modules.
 *
 * @type {{test: RegExp, exclude: RegExp, use: string[]}}
 */
const ScssLoader = {
  test: /\.scss$/,
  include: [
    PATHS.assets
  ],
  exclude: [/\.module\.scss$/],
  use: [
    'style-loader',
    'css-loader',
    'resolve-url-loader',
    'sass-loader'
  ]
};

/**
 * Loader that handles .scss files that are css modules.
 * Those files has .module.scss extension and are used as
 * style files for react components.
 *
 * @type {{test: RegExp, exclude: RegExp, use: string[]}}
 */
const ScssModulesLoader = {
  test: /\.module\.scss$/,
  include: [
    PATHS.assets,
  ],
  use: [
    'style-loader',
    {
      loader: 'css-loader',
      query: {
        modules: true,
        importLoaders: true,
        localIdentName: '[name]__[local]___[hash:base64:5]',
      },
    },
    'resolve-url-loader',
    'sass-loader',
  ],
};

/**
 * Configuration for Webpack development server.
 */
const WebpackDevServerConfig = {
  // Set up dev server host
  host: WebpackDevServerSettings.host,

  // Set up dev server port
  port: WebpackDevServerSettings.port,

  // Add header to allow CORS requests to webpack dev server
  // - since we use it as proxy requests will be coming from
  // another domain so CORS must be enabled.
  headers: {
    'Access-Control-Allow-Origin': '*',
  },

  // Hot reload
  hot: true,
  hotOnly: true
};

// Export webpack config
module.exports = {
  // Define entries for javascript and style files.
  entry: {
    // Main entry for all files
    "bundle": [
      'babel-polyfill',
      'react-hot-loader/patch', // Patch for react hot loader
      PATHS.scripts + '/main.js'
    ],
  },

  // Define output for webpack
  output: {
    // Output directory path
    path: PATHS.build,

    // Output file name as [entry-point-name].min.js
    filename: '[name].min.js',

    publicPath: 'http://localhost:10001/'
  },

  // Define source maps so bundled code could be split to parts
  devtool: 'source-map',

  // Dev server
  devServer: WebpackDevServerConfig,

  // Adds resolve
  resolve,

  // Set up loaders for files
  module: {
    // Define rules for files
    rules: [
      ImagesLoader,
      JavascriptLoader,
      ScssLoader,
      ScssModulesLoader,
    ],
  },

  // Define plugins
  plugins: [
    new webpack.NoEmitOnErrorsPlugin(),
    new webpack.NamedModulesPlugin(),
    plugins.InitProvidePlugin()
  ]
};
