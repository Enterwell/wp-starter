// Imports
const webpack = require('webpack');

/**
 * Inits the provide plugin.
 *
 * @returns new isntance of provide plugin.
 */
function InitProvidePlugin() {
  // Returns the provide plugin
  return new webpack.ProvidePlugin({
    $: 'jquery',
    jQuery: 'jquery',
    gsap: 'gsap',
    ScrollMagic: 'ScrollMagic',
    debounce: ['lodash', 'debounce']
  });
}

// Export
module.exports = {
  InitProvidePlugin
};
