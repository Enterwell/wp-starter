// Require Encore
const Encore = require('@symfony/webpack-encore');
// Import settings
const settings = require('./webpack.settings');

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
  const name = file.replace(/\.js/, '');
  Encore.addEntry(name, filePath);
});

// Encore settings setup
Encore
  .setOutputPath(settings.PATHS.build)

  .setPublicPath('/')

  .cleanupOutputBeforeBuild()

  .addStyleEntry('app_styles', settings.PATHS.styles + '/app.scss')

  .splitEntryChunks()

  .enableSingleRuntimeChunk()

  .cleanupOutputBeforeBuild()

  .enableBuildNotifications()

  .enableSourceMaps(!Encore.isProduction())

  .enableVersioning(Encore.isProduction())

  // .configureBabelPresetEnv((config) => {
  //  config.useBuiltIns = 'usage';
  //  config.corejs = 3;
  // })

  .enableSassLoader()

  //.autoProvidejQuery()

  .autoProvideVariables({
    $: 'jquery',
    jQuery: 'jquery'
  })

  .configureDevServerOptions(options => {
    options.allowedHosts = 'all';
    options.port = settings.WebpackDevServerSettings.port;
    delete options.client.host;
  })
;

module.exports = Encore.getWebpackConfig();
