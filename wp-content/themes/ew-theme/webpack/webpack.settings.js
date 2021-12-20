const themeConfig = require('../theme-config');

const path = require('path');

const PATHS = {
  root: path.join(__dirname, '../'),
  assets: path.join(__dirname, '../assets'),
  styles: path.join(__dirname, '../assets/styles'),
  fonts: path.join(__dirname, '../assets/styles/fonts'),
  scripts: path.join(__dirname, '../assets/js'),
  build: path.join(__dirname, '../assets/dist'),
  gutenberg: path.join(__dirname, '../assets/gutenberg')
};
/**
 * Settings for webpack dev server.
 *
 * @link https://webpack.js.org/configuration/dev-server/
 * @type {{port: number, address: string}}
 */
const WebpackDevServerSettings = {
  port: themeConfig.webpackPort,
  host: themeConfig.webpackHost,
  address: 'http://' + themeConfig.webpackHost + ':' + themeConfig.webpackPort + '/',
};

/**
 * Settings for app server. App server is server
 * that serves our main app (apache/nginx server for PHP files)
 *
 * @type {{address: string}}
 */
const WebAppServerSettings = {
  address: themeConfig.webAppServerAddress,
  themeName: themeConfig.themeName
};

module.exports = {
  PATHS,
  WebpackDevServerSettings,
  WebAppServerSettings
};
