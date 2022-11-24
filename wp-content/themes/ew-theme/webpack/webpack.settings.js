const themeConfig = require('../theme-config');

const path = require('path');
var os = require('os');

/**
 * Gets the local IP address or 127.0.0.1
 * It returns the first address that starts with 192.168.0. or 192.168.1.
 * @returns string
 */
function getLocalIp() {
  var interfaces = os.networkInterfaces();
  var addresses = [];
  for (var k in interfaces) {
      for (var k2 in interfaces[k]) {
          var address = interfaces[k][k2];
          if (address.family === 'IPv4' && !address.internal) {
              addresses.push(address.address);
          }
      }
  }

  // Iterates over the addreses
  for (const address of addresses) {
    if (
      address.startsWith('192.168.0.') ||
      address.startsWith('192.168.1.')
    ) {
      return address;
    }
  }

  // Default - returns home address
  return '127.0.0.1';
}

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
const MY_IP = getLocalIp();
const WebpackDevServerSettings = {
  port: themeConfig.webpackPort,
  host: '0.0.0.0',
  address: 'http://' + MY_IP + ':' + themeConfig.webpackPort + '/',
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
