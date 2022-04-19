// Define all external components used in Gutenberg.
const wplib = [
  'components',
  'compose',
  'dispatch',
  'blocks',
  'element',
  'editor',
  'date',
  'data',
  'i18n',
  'keycodes',
  'viewport',
  'blob',
  'url',
  'apiFetch',
  'plugins',
  'editPost',
  'blockEditor',
  'richText'
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
  return ext;
})();

module.exports = externals;
