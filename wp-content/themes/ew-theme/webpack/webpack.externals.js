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
  'editPost'
];

// Add all Gutenberg external libs so you can use it like @wordpress/lib_name.
const externals = (function() {
  const ext = {};
  wplib.forEach((name) => {
    ext[`@wp/${name}`] = `wp.${name}`;
    ext[`@wordpress/${name}`] = `wp.${name}`;
  });
  return ext;
})();

module.exports = externals;
