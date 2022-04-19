const fs = require('fs');
const mkdirp = require('mkdirp');

console.log('\n');
console.log(`
 _____      _                         _ _ 
|  ___|    | |                       | | |
| |__ _ __ | |_ ___ _ ____      _____| | |
|  __| '_ \\| __/ _ \\ '__\\ \\ /\\ / / _ \\ | |
| |__| | | | ||  __/ |   \\ V  V /  __/ | |
\\____/_| |_|\\__\\___|_|    \\_/\\_/ \\___|_|_|
`);
console.log('Enterwell\'s Gutenberg format type generator');
console.log('Version: v1.0');
console.log('///////////////////////////////////////////////');

const TEMPLATES = {
	formatScript: '.scripts/templates/format-type/format.js',
	formatAdminStyle: '.scripts/templates/format-type/format.module.scss',
	formatPublicStyle: '.scripts/templates/format-type/format.scss'
};

const formatsFolder = './assets/gutenberg/format-types';

if (!fs.existsSync(formatsFolder)) {
	console.error('[FORMAT_CREATE_ERROR] Format types folder does not exist!');
	return;
}

// Get script args
const args = process.argv.slice(2);
if (args.length !== 1) {
	console.error('[FORMAT_CREATE_ERROR] Format type name is not specified!');
	return;
}

// Get format name
const formatName = args[0];

// Validate kebab-case format name
if (!/^([a-z](?![\d])|[\d](?![a-z]))+(-?([a-z](?![\d])|[\d](?![a-z])))*$|^$/.test(formatName)) {
	console.error('[FORMAT_CREATE_ERROR] Format type name needs to be in kebab-case!');
	return;
}

// Create specific names for all naming conventions needed
const formatNameKebab = formatName;
const formatNameCamel = camelize(formatNameKebab);
const formatNamePascal = capitalise(formatNameCamel);

const formatFolder = formatsFolder + '/' + formatName;
if (fs.existsSync(formatFolder)) {
	console.error('[FORMAT_CREATE_ERROR] Format type with same name already exists (' + formatName + ')!');
	return;
}

console.log('///// Creating folder structure...');

mkdirp.sync(formatFolder);

console.log('///// Writing default files.');

writeFiles();

console.log('///// Format type created.');

/**
 * Kebab-case to camelCase
 * @param string
 * @returns {*}
 */
function camelize(string) {
	return string.replace(/-./g, x => x[1].toUpperCase());
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
 * Replace placeholder strings with format type names
 * @param string
 * @param nameKebab
 * @param nameCamel
 * @param namePascal
 * @returns {*}
 */
function replacePlaceholders(string, nameKebab, nameCamel, namePascal) {
	return string.replace(/FORMATKEBAB/g, nameKebab).replace(/FORMATCAMEL/g, nameCamel).replace(/FORMATPASCAL/g, namePascal);
}

/**
 * Creates format type files
 */
function writeFiles() {
	let fileContent = fs.readFileSync(TEMPLATES.formatScript, 'utf8');
	let file = formatFolder + '/' + formatNameKebab + '.js';

	fs.writeFileSync(file, replacePlaceholders(fileContent, formatNameKebab, formatNameCamel, formatNamePascal));
	console.log('Format type script: ' + file);

	fileContent = fs.readFileSync(TEMPLATES.formatAdminStyle, 'utf8');
	file = formatFolder + '/' + formatNameKebab + '.module.scss';

	fs.writeFileSync(file, replacePlaceholders(fileContent, formatNameKebab, formatNameCamel, formatNamePascal));
	console.log('Format type admin styles: ' + file);

	fileContent = fs.readFileSync(TEMPLATES.formatPublicStyle, 'utf8');
	file = formatFolder + '/' + formatNameKebab + '.scss';

	fs.writeFileSync(file, replacePlaceholders(fileContent, formatNameKebab, formatNameCamel, formatNamePascal));
	console.log('Format type public styles: ' + file);

	const gutenbergStylesFile = formatsFolder + '/format-type-styles.scss';
	let gutenbergStyles = fs.readFileSync(gutenbergStylesFile, 'utf8');
	gutenbergStyles += '\r\n';
	gutenbergStyles += '@import "' + formatNameKebab + '/' + formatNameKebab + '.scss";';
	fs.writeFileSync(gutenbergStylesFile, replacePlaceholders(gutenbergStyles, formatNameKebab, formatNameCamel, formatNamePascal));
	console.log('format-type-styles.scss updated...');
}