// Require Encore
const Encore = require('@symfony/webpack-encore');
// Import settings
const settings = require('./webpack.settings');
// Include externals
const externals = require('./webpack.externals');
// Include open plugin
const {WebpackOpenBrowser} = require('webpack-open-browser');

const chokidar = require('chokidar');
const glob = require('glob');

// Environment setup
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

// Get production publicPath
const prodPublicPath = `/wp-content/themes/${settings.WebAppServerSettings.themeName}/assets/dist`;

////////////////////////////////////////////////////
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
////////////////////////////////////////////////////

////////////////////////////////////////////////////
// Finds all gutenberg format type scripts
const gutenbergFormatTypeScriptEntries = glob.sync('format-types/**/*.js', {
	'cwd': settings.PATHS.gutenberg
});

// Combines gutenberg format type scripts in one entry
let gutenbergFormatTypeScripts = [];
gutenbergFormatTypeScriptEntries.forEach((file) => {
	const filePath = settings.PATHS.gutenberg + '/' + file;
	gutenbergFormatTypeScripts.push(filePath);
});

// Add gutenberg format type scripts as one entry if any
gutenbergFormatTypeScripts.length && Encore.addEntry('gutenberg_admin_format_types', gutenbergFormatTypeScripts);
////////////////////////////////////////////////////

////////////////////////////////////////////////////
// Finds all gutenberg component scripts
const gutenbergComponentScriptEntries = glob.sync('components/**/*.js', {
	'cwd': settings.PATHS.gutenberg
});

// Combines gutenberg component scripts in one entry
let gutenbergComponentScripts = [];
gutenbergComponentScriptEntries.forEach((file) => {
	const filePath = settings.PATHS.gutenberg + '/' + file;
	gutenbergComponentScripts.push(filePath);
});

// Add gutenberg component scripts as one entry if any
gutenbergComponentScripts.length && Encore.addEntry('gutenberg_admin_components', gutenbergComponentScripts);
////////////////////////////////////////////////////

////////////////////////////////////////////////////
// Finds all public gutenberg block scripts
const gutenbergBlockScriptEntries = glob.sync('blocks/**/*.js', {
  'cwd': settings.PATHS.gutenberg,
  'ignore': [
    '**/admin/**/*.js',
  ]
});

// Adds each public gutenberg block script as separate script entry
gutenbergBlockScriptEntries.forEach((file) => {
  const filePath = settings.PATHS.gutenberg + '/' + file;
	const name = file.replace(/(\.jsx)|(\.js)/, '');
	Encore.addEntry(name, filePath);
});
////////////////////////////////////////////////////

// Encore settings setup
Encore
  .setOutputPath(settings.PATHS.build)

  .setPublicPath(prodPublicPath)

  .cleanupOutputBeforeBuild()

  // Gutenberg admin script entry
  .addEntry('gutenberg_admin_blocks', settings.PATHS.gutenberg + '/index.js')

  // Admin styles entry
  .addStyleEntry('editor_styles', settings.PATHS.styles + '/common/editor.scss')

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
    options.host = `${settings.WebpackDevServerSettings.host}`;
    options.port = `${settings.WebpackDevServerSettings.port}`;
    delete options.client;
  })
;

const config = Encore.getWebpackConfig();

// Manual override due to incompatibility of Webpack Encore with Webpack Dev server in latest version
// TODO: check this later
if (!Encore.isProduction()) {
  config.output.publicPath = settings.WebpackDevServerSettings.address;
}

// Watches for changes in twig and PHP files inside theme
if (!Encore.isProduction()) {
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
}

module.exports = config;
