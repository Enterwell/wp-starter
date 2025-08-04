const fs = require('fs');
const mkdirp = require('mkdirp');
const projectManifest = require('../assets/gutenberg/manifest.json');

console.log('\n');
console.log(`
 _____      _                         _ _
|  ___|    | |                       | | |
| |__ _ __ | |_ ___ _ ____      _____| | |
|  __| '_ \\| __/ _ \\ '__\\ \\ /\\ / / _ \\ | |
| |__| | | | ||  __/ |   \\ V  V /  __/ | |
\\____/_| |_|\\__\\___|_|    \\_/\\_/ \\___|_|_|
`);
console.log('Enterwell\'s Gutenberg block generator');
console.log('Version: v2.0');
console.log('///////////////////////////////////////////////');

const TEMPLATES = {
  blockEdit: '.scripts/templates/block/block.js',
	blockEditor: '.scripts/templates/block/block-editor.js',
	blockOptions: '.scripts/templates/block/block-options.js',
	blockToolbar: '.scripts/templates/block/block-toolbar.js',
  blockAdminStyle: '.scripts/templates/block/block.module.scss',
  blockTwig: '.scripts/templates/block/block.twig',
  blockPublicStyle: '.scripts/templates/block/block.scss',
  blockScript: '.scripts/templates/block/block-public.js',
};

const blocksFolder = './assets/gutenberg/blocks';

if (!fs.existsSync(blocksFolder)) {
  console.error('[BLOCK_CREATE_ERROR] Blocks folder does not exist!');
  return;
}

// Get script args
const args = process.argv.slice(2);
if (args.length !== 1) {
  console.error('[BLOCK_CREATE_ERROR] Block name is not specified!');
  return;
}

// Get block name
const blockName = args[0];

// Validate kebab-case block name
if(!/^([a-z](?![\d])|[\d](?![a-z]))+(-?([a-z](?![\d])|[\d](?![a-z])))*$|^$/.test(blockName)) {
	console.error('[BLOCK_CREATE_ERROR] Block name needs to be in kebab-case format!');
	return;
}

// Create specific names for all naming conventions needed
const blockNameKebab = blockName;
const blockNameCamel = camelize(blockNameKebab);
const blockNamePascal = capitalise(blockNameCamel);

const blockFolder = blocksFolder + '/' + blockName;
if (fs.existsSync(blockFolder)) {
  console.error('[BLOCK_CREATE_ERROR] Block with same name already exists (' + blockName + ')!');
  return;
}

// Create folder structure
console.log('///// Creating folder structure...');
const adminFolder = blockFolder + '/admin';
const publicFolder = blockFolder + '/public';

mkdirp.sync(adminFolder);
mkdirp.sync(publicFolder);

console.log('///// Writing default files.');

writeManifest();
writeAdminFiles();
writePublicFiles();

console.log('///// Block created.');

/**
 * Kebab-case to camelCase
 * @param string
 * @returns {*}
 */
function camelize(string) {
  return string.replace(/-./g, x=>x[1].toUpperCase());
}

/**
 * Capitalise string
 * @param string
 * @returns {string}
 */
function capitalise(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}

/**
 * Replace placeholder strings with block names
 * @param string
 * @param nameKebab
 * @param nameCamel
 * @param namePascal
 * @returns {*}
 */
function replacePlaceholders(string, nameKebab, nameCamel, namePascal) {
	return string.replace(/BLOCKKEBAB/g, nameKebab).replace(/BLOCKCAMEL/g, nameCamel).replace(/BLOCKPASCAL/g, namePascal);
}

/**
 * Creates block manifest file
 */
function writeManifest() {
  // Default manifest
  const defaultManifest = {
    blockName: blockNameKebab,
    category: projectManifest.blocksCategory.slug,
    title: '',
    hasInnerBlocks: false,
    keywords: [],
    styles: [],
    supports: {},
    description: '',
    icon: 'block-default',
  };

  const manifestFile = blockFolder + '/manifest.json';
  fs.writeFileSync(manifestFile, JSON.stringify(defaultManifest, null, 2));
  console.log('Manifest: ' + manifestFile);
}

/**
 * Creates block admin files
 */
function writeAdminFiles() {
  const partialsFolder = adminFolder + '/partials';
	mkdirp.sync(partialsFolder);

  let fileContent = fs.readFileSync(TEMPLATES.blockEditor, 'utf8');
	let file = adminFolder + '/partials/' + blockNameKebab + '-editor.js';

	fs.writeFileSync(file, replacePlaceholders(fileContent, blockNameKebab, blockNameCamel, blockNamePascal));
	console.log('Block editor script: ' + file);

	fileContent = fs.readFileSync(TEMPLATES.blockOptions, 'utf8');
	file = adminFolder + '/partials/' + blockNameKebab + '-options.js';

	fs.writeFileSync(file, replacePlaceholders(fileContent, blockNameKebab, blockNameCamel, blockNamePascal));
	console.log('Block options script: ' + file);

	fileContent = fs.readFileSync(TEMPLATES.blockToolbar, 'utf8');
	file = adminFolder + '/partials/' + blockNameKebab + '-toolbar.js';

	fs.writeFileSync(file, replacePlaceholders(fileContent, blockNameKebab, blockNameCamel, blockNamePascal));
	console.log('Block toolbar script: ' + file);

	fileContent = fs.readFileSync(TEMPLATES.blockEdit, 'utf8');
	file = adminFolder + '/' + blockNameKebab + '.js';

	fs.writeFileSync(file, replacePlaceholders(fileContent, blockNameKebab, blockNameCamel, blockNamePascal));
	console.log('Block edit script: ' + file);

	fileContent = fs.readFileSync(TEMPLATES.blockAdminStyle, 'utf8');
	file = adminFolder + '/' + blockNameKebab + '.module.scss';

	fs.writeFileSync(file, replacePlaceholders(fileContent, blockNameKebab, blockNameCamel, blockNamePascal));
	console.log('Block admin styles: ' + file);
}

/**
 * Creates block public files
 */
function writePublicFiles() {
  let fileContent = fs.readFileSync(TEMPLATES.blockTwig, 'utf8');
  let file = publicFolder + '/' + blockNameKebab + '.twig';

  fs.writeFileSync(file, replacePlaceholders(fileContent, blockNameKebab, blockNameCamel, blockNamePascal));
  console.log('Block twig: ' + file);

	fileContent = fs.readFileSync(TEMPLATES.blockPublicStyle, 'utf8');
	file = publicFolder + '/' + blockNameKebab + '.scss';

	fs.writeFileSync(file, replacePlaceholders(fileContent, blockNameKebab, blockNameCamel, blockNamePascal));
	console.log('Block public styles: ' + file);

	fileContent = fs.readFileSync(TEMPLATES.blockScript, 'utf8');
	file = publicFolder + '/' + blockNameKebab + '.js';

	fs.writeFileSync(file, replacePlaceholders(fileContent, blockNameKebab, blockNameCamel, blockNamePascal));
	console.log('Block script: ' + file);

	const gutenbergStylesFile = blocksFolder + '/block-styles.scss';
	let gutenbergStyles = fs.readFileSync(gutenbergStylesFile, 'utf8');
	gutenbergStyles += '\r\n';
	gutenbergStyles += '@import "' + blockNameKebab + '/public/' + blockNameKebab + '.scss";';
	fs.writeFileSync(gutenbergStylesFile, replacePlaceholders(gutenbergStyles, blockNameKebab, blockNameCamel, blockNamePascal));
	console.log('block-styles.scss updated...');
}
