// Require Encore
const Encore = require('@symfony/webpack-encore');
// Import settings
const settings = require('./webpack.settings');
// Include externals
const externals = require('./webpack.externals');
// Include open plugin
const { WebpackOpenBrowser } = require('webpack-open-browser');

const path = require('path');
const glob = require('glob');

// Constants
const ENV_DEVELOPMENT = 'dev';
const ENV_PRODUCTION = 'prod';

// Environment setup
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || ENV_DEVELOPMENT);
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

// Finds all gutenberg scripts
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

  // Styles entry
  .addStyleEntry('app_styles', settings.PATHS.styles + '/app.scss')

  // Gutenberg admin script entry
  .addEntry('gutenberg_admin', settings.PATHS.gutenberg + '/index.js')

  // Gutenberg public script entry
  .addEntry('gutenberg_public', gutenbergScripts)

  .splitEntryChunks()

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

  .autoProvideVariables({
    $: 'jquery',
    jQuery: 'jquery',
    gsap: 'gsap',
    ScrollMagic: 'ScrollMagic',
    debounce: ['lodash', 'debounce']
  })

  .addAliases({
    TweenLite: path.resolve('node_modules', 'gsap/src/minified/TweenLite.min.js'),
    TweenMax: path.resolve('node_modules', 'gsap/src/minified/TweenMax.min.js'),
    TimelineLite: path.resolve('node_modules', 'gsap/src/minified/TimelineLite.min.js'),
    TimelineMax: path.resolve('node_modules', 'gsap/src/minified/TimelineMax.min.js'),
    ScrollMagic: path.resolve('node_modules', 'scrollmagic/scrollmagic/minified/ScrollMagic.min.js'),
    "animation.gsap": path.resolve('node_modules', 'scrollmagic/scrollmagic/minified/plugins/animation.gsap.min.js'),
    "debug.addIndicators": path.resolve('node_modules', 'scrollmagic/scrollmagic/minified/plugins/debug.addIndicators.min.js')
  })

  .addExternals(externals)

  .enableSassLoader()

  .enableReactPreset()

  .addPlugin(new WebpackOpenBrowser({
    url: settings.WebAppServerSettings.address
  }))

  .configureDevServerOptions(options => {
    options.allowedHosts = 'all';
    options.hot = true;
    options.port = `${settings.WebpackDevServerSettings.port}`;
    delete options.client.host;
  })
;

const config = Encore.getWebpackConfig();

console.log(config);

// Manual override due to incompatibility of Webpack Encore with Webpack Dev server in latest version
// TODO: check this later
config.output.publicPath = settings.WebpackDevServerSettings.address;

module.exports = config;
