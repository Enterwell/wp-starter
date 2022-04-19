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
console.log('Enterwell\'s Gutenberg component generator');
console.log('Version: v1.0');
console.log('///////////////////////////////////////////////');

const TEMPLATES = {
	componentEditor: '.scripts/templates/component/component.js',
	componentOptions: '.scripts/templates/component/component-options.js',
	componentToolbar: '.scripts/templates/component/component-toolbar.js',
	componentAdminStyle: '.scripts/templates/component/component.module.scss',
	componentTwig: '.scripts/templates/component/component.twig',
	componentPublicStyle: '.scripts/templates/component/component.scss'
};

const componentsFolder = './assets/gutenberg/components';

if (!fs.existsSync(componentsFolder)) {
	console.error('[COMPONENT_CREATE_ERROR] Components folder does not exist!');
	return;
}

// Get script args
const args = process.argv.slice(2);
if (args.length !== 1) {
	console.error('[BLOCK_CREATE_ERROR] Component name is not specified!');
	return;
}

// Get component name
const componentName = args[0];

// Validate kebab-case component name
if (!/^([a-z](?![\d])|[\d](?![a-z]))+(-?([a-z](?![\d])|[\d](?![a-z])))*$|^$/.test(componentName)) {
	console.error('[COMPONENT_CREATE_ERROR] Component name needs to be in kebab-case format!');
	return;
}

// Create specific names for all naming conventions needed
const componentNameKebab = componentName;
const componentNameCamel = camelize(componentNameKebab);
const componentNamePascal = capitalise(componentNameCamel);

const componentFolder = componentsFolder + '/' + componentName;
if (fs.existsSync(componentFolder)) {
	console.error('[COMPONENT_CREATE_ERROR] Component with same name already exists (' + componentName + ')!');
	return;
}

// Create folder structure
console.log('///// Creating folder structure...');
const adminFolder = componentFolder + '/admin';
const publicFolder = componentFolder + '/public';

mkdirp.sync(adminFolder);
mkdirp.sync(publicFolder);

console.log('///// Writing default files.');

writeManifest();
writeAdminFiles();
writePublicFiles();

console.log('///// Component created.');

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
 * Replace placeholder strings with component names
 * @param string
 * @param nameKebab
 * @param nameCamel
 * @param namePascal
 * @returns {*}
 */
function replacePlaceholders(string, nameKebab, nameCamel, namePascal) {
	return string.replace(/COMPONENTKEBAB/g, nameKebab).replace(/COMPONENTCAMEL/g, nameCamel).replace(/COMPONENTPASCAL/g, namePascal);
}

/**
 * Creates block manifest file
 */
function writeManifest() {
	// Default manifest
	const defaultManifest = {
		componentName: componentNameKebab,
		title: '',
		description: ''
	};

	const manifestFile = componentFolder + '/manifest.json';
	fs.writeFileSync(manifestFile, JSON.stringify(defaultManifest, null, 2));
	console.log('Manifest: ' + manifestFile);
}

/**
 * Creates component admin files
 */
function writeAdminFiles() {
	let fileContent = fs.readFileSync(TEMPLATES.componentEditor, 'utf8');
	let file = adminFolder + '/' + componentNameKebab + '.js';

	fs.writeFileSync(file, replacePlaceholders(fileContent, componentNameKebab, componentNameCamel, componentNamePascal));
	console.log('Component editor script: ' + file);

	fileContent = fs.readFileSync(TEMPLATES.componentOptions, 'utf8');
	file = adminFolder + '/' + componentNameKebab + '-options.js';

	fs.writeFileSync(file, replacePlaceholders(fileContent, componentNameKebab, componentNameCamel, componentNamePascal));
	console.log('Component options script: ' + file);

	fileContent = fs.readFileSync(TEMPLATES.componentToolbar, 'utf8');
	file = adminFolder + '/' + componentNameKebab + '-toolbar.js';

	fs.writeFileSync(file, replacePlaceholders(fileContent, componentNameKebab, componentNameCamel, componentNamePascal));
	console.log('Component toolbar script: ' + file);

	fileContent = fs.readFileSync(TEMPLATES.componentAdminStyle, 'utf8');
	file = adminFolder + '/' + componentNameKebab + '.module.scss';

	fs.writeFileSync(file, replacePlaceholders(fileContent, componentNameKebab, componentNameCamel, componentNamePascal));
	console.log('Component admin styles: ' + file);
}

/**
 * Creates component public files
 */
function writePublicFiles() {
	let fileContent = fs.readFileSync(TEMPLATES.componentTwig, 'utf8');
	let file = publicFolder + '/' + componentNameKebab + '.twig';

	fs.writeFileSync(file, replacePlaceholders(fileContent, componentNameKebab, componentNameCamel, componentNamePascal));
	console.log('Component twig: ' + file);

	fileContent = fs.readFileSync(TEMPLATES.componentPublicStyle, 'utf8');
	file = publicFolder + '/' + componentNameKebab + '.scss';

	fs.writeFileSync(file, replacePlaceholders(fileContent, componentNameKebab, componentNameCamel, componentNamePascal));
	console.log('Component public styles: ' + file);

	const gutenbergStylesFile = componentsFolder + '/component-styles.scss';
	let gutenbergStyles = fs.readFileSync(gutenbergStylesFile, 'utf8');
	gutenbergStyles += '\r\n';
	gutenbergStyles += '@import "' + componentNameKebab + '/public/' + componentNameKebab + '.scss";';
	fs.writeFileSync(gutenbergStylesFile, replacePlaceholders(gutenbergStyles, componentNameKebab, componentNameCamel, componentNamePascal));
	console.log('component-styles.scss updated...');
}