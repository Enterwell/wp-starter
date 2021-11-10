// Require Encore
const Encore = require('@symfony/webpack-encore');
// Import settings
const settings = require('./webpack.settings');
// Include externals
const externals = require('./webpack.externals');
// Include open plugin
const { WebpackOpenBrowser } = require('webpack-open-browser');

const chokidar = require('chokidar');
const glob = require('glob');

// Environment setup
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

// Finds all scripts inside /js folder
const scriptEntries = glob.sync('**/*.+(js|jsx)', {
  'cwd': settings.PATHS.scripts
});

// Adds each script as separate script entry
scriptEntries.forEach((file) => {
  const filePath = settings.PATHS.scripts + '/' + file;
  const name = file.replace(/(\.jsx)|(\.js)/, '');
  Encore.addEntry(name, filePath);
});

// Finds all public gutenberg scripts
const gutenbergScriptEntries = glob.sync('**/*.js', {
  'cwd': settings.PATHS.gutenberg,
  'ignore': [
    'blocks/**/admin/*.js',
    'index.js'
  ]
});

// Combines gutenberg block scripts in one entry
let gutenbergScripts = [];
gutenbergScriptEntries.forEach((file) => {
  const filePath = settings.PATHS.gutenberg + '/' + file;
  const name = file.replace(/\.js/, '');
  gutenbergScripts.push(filePath);
});

// Add gutenberg script as entry if any
!gutenbergScripts.length && Encore.addEntry('gutenberg_public', gutenbergScripts);

// Encore settings setup
Encore
  .setOutputPath(settings.PATHS.build)

  .setPublicPath('/')

  .cleanupOutputBeforeBuild()

  // Gutenberg admin script entry
  .addEntry('gutenberg_admin', settings.PATHS.gutenberg + '/index.js')

  // Gutenberg public script entry
  .addEntry('gutenberg_public', gutenbergScripts)

  .enableSingleRuntimeChunk()

  .enableBuildNotifications()

  .enableSourceMaps(!Encore.isProduction())

  .enableVersioning(Encore.isProduction())

  .configureBabel(config => {
    config.plugins.push(['@babel/plugin-proposal-decorators', {'legacy': true}]);
    config.plugins.push('react-hot-loader/babel');
  })

  .configureBabelPresetEnv((config) => {
   config.useBuiltIns = 'usage';
   config.corejs = 2;
  })

  .addExternals(externals)

  .enableSassLoader()

  .enableReactPreset()

  .addPlugin(new WebpackOpenBrowser({
    url: settings.WebAppServerSettings.address
  }))

  .configureDevServerOptions(options => {
    options.allowedHosts = 'all';
    options.port = `${settings.WebpackDevServerSettings.port}`;
    delete options.client;
  })
;

const config = Encore.getWebpackConfig();

// Manual override due to incompatibility of Webpack Encore with Webpack Dev server in latest version
// TODO: check this later
config.output.publicPath = settings.WebpackDevServerSettings.address;

// Watches for changes in twig and PHP files inside theme
config.devServer.onBeforeSetupMiddleware = (server) => {
  chokidar.watch([
    '../**/*.twig',
    '../**/*.php'
  ]).on('all', () => {
    for (const ws of server.webSocketServer.clients) {
      ws.send('{"type": "static-changed"}')
    }
  })
}

module.exports = config;
