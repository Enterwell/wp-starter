// Define all external components used in Gutenberg.
const wplib = [
  'apiFetch',
  'blob',
  'blockEditor',
  'blocks',
  'components',
  'compose',
  'dispatch',
  'data',
  'date',
  'domReady',
  'element',
  'editor',
  'editPost',
  'i18n',
  'keycodes',
  'plugins',
  'richText',
  'url',
  'viewport'
];

/**
 * Transforms camelCase to kebab-case
 * @param str
 * @returns {*}
 */
const kebabize = (str) => str.replace(/[A-Z]+(?![a-z])|[A-Z]/g, ($, ofs) => (ofs ? "-" : "") + $.toLowerCase());

// Add all Gutenberg external libs so you can use it like @wordpress/lib-name.
const externals = (function() {
  const ext = {};
  wplib.forEach((name) => {
    ext[`@wordpress/${kebabize(name)}`] = `wp.${name}`;
  });
  ext['jquery'] = 'jQuery';
  return ext;
})();

module.exports = externals;
