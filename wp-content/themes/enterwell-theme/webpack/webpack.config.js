// Require development configuration
const developmentConfig = require('./webpack.development');

// Require production configuration
const productionConfig = require('./webpack.production');


// Constants
const ENV_DEVELOPMENT = 'development';
const ENV_PRODUCTION = 'production';

module.exports = function () {
    const env = process.env.NODE_ENV || ENV_DEVELOPMENT;
    process.env.BABEL_ENV = env;

    return env === ENV_PRODUCTION ? productionConfig : developmentConfig;
};