const ExtractTextPlugin = require('extract-text-webpack-plugin');
const webpack = require('webpack');
const autoprefixer = require('autoprefixer');

// Include settings
const settings = require('./webpack.settings');

const PATHS = settings.PATHS;
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
  test: /^((?!module\.scss).)*\.scss$/,
  exclude: [/node_modules/, PATHS.fonts],
  use: ExtractTextPlugin.extract({
    fallback: 'style-loader',
    use: [
      {
        loader: 'css-loader',
        options: {
          minimize: true
        }
      },
      {
        loader: 'postcss-loader',
        options: {
          ident: 'postcss',
          sourceMap: true,
          plugins() {
            return [autoprefixer];
          }
        }
      },
      'resolve-url-loader',
      'sass-loader'
    ]
  })
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
  exclude: [/node_modules/, PATHS.fonts],
  use: ExtractTextPlugin.extract({
    fallback: 'style-loader',
    use: [
      {
        loader: 'css-loader',
        query: {
          modules: true,
          importLoaders: true,
          localIdentName: '[name]__[hash:6]',
          minimize: true
        }
      },
      {
        loader: 'postcss-loader',
        options: {
          ident: 'postcss',
          sourceMap: true,
          plugins() {
            return [autoprefixer];
          }
        }
      },
      'resolve-url-loader',
      'sass-loader'
    ]
  })
};


// Export webpack config
module.exports = {

  // Define entries for javascript and style files.
  entry: {

    // Main entry for all files
    bundle: [
      'babel-polyfill',
      'react-hot-loader/patch',
      PATHS.scripts + '/main.js'
    ],
  },

  // Define output for webpack
  output: {

    // Output directory path
    path: PATHS.build,

    // Output file name as [entry-point-name].min.js
    filename: '[name].min.js'
  },

  // Add .jsx files to auto resolve
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
    ]
  },

  // Define plugins
  plugins: [
    new ExtractTextPlugin('[name].min.css'),
    plugins.InitProvidePlugin(),
    new webpack.DefinePlugin({
      'process.env': {
        NODE_ENV: JSON.stringify('production')
      }
    }),
    new webpack.optimize.UglifyJsPlugin()
  ]
};
