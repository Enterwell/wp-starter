// Imports
const path = require('path');

/**
 * Paths for the gulp config.
 */
const PATHS = {
  root: path.join(__dirname, '../')
};
PATHS.assets = path.join(PATHS.root, '/assets');
PATHS.styles = path.join(PATHS.assets, '/styles');
PATHS.dist = path.join(PATHS.assets, '/dist');

// Export
module.exports = {
  PATHS
};
