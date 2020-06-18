const fs = require('fs');
const mkdirp = require('mkdirp');
const projectManifest = require('../assets/gutenberg/manifest.json');

const TEMPLATES = {
  editComponent: '.scripts/templates/edit.js',
  twig: '.scripts/templates/public.twig',
  scss: '.scripts/templates/style.scss',
};

const gutenbergFolder = './assets/gutenberg';
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

// TODO: Validate block name

const blockFolder = blocksFolder + '/' + blockName;
if (fs.existsSync(blockFolder)) {
  console.error('[BLOCK_CREATE_ERROR] Block with same name already exists (' + blockName + ')!');
  return;
}

// Create folder structure
console.log('Creating folder structure...');
const adminFolder = blockFolder + '/admin';
const publicFolder = blockFolder + '/public';

mkdirp.sync(adminFolder);
mkdirp.sync(publicFolder);

console.log('Writing default files.');

writeManifest(blockFolder);
writeDefaultComponent(blockName, adminFolder);
writeDefaultTwig(blockName, publicFolder);
writeDefaultStyle(blockName, publicFolder, gutenbergFolder);

console.log('Block created.');

function ucwfirst(word) {
  return word.charAt(0).toUpperCase() + word.slice(1);
}

function writeManifest(manifestRootFolder) {
  const defaultManifest = {
    blockName: blockName,
    category: projectManifest.blocksCategory.slug,
    title: '',
    hasInnerBlocks: false,
    keywords: [],
    styles: [],
    description: '',
  };

  const manifestFile = manifestRootFolder + '/manifest.json';
  fs.writeFileSync(manifestFile, JSON.stringify(defaultManifest, null, 2));
  console.log('Manifest: ' + manifestFile);

}

function writeDefaultComponent(blockName, fileFolder) {
  const componentName = blockName.split('-').map(ucwfirst).join('');
  const fileContent = fs.readFileSync(TEMPLATES.editComponent, 'utf8');
  const file = fileFolder + '/' + blockName + '.js';

  fs.writeFileSync(file, fileContent.replace(/COMPONENT/g, componentName));
  console.log('Edit component: ' + file);
}

function writeDefaultTwig(blockName, fileFolder) {
  const fileContent = fs.readFileSync(TEMPLATES.twig, 'utf8');
  const file = fileFolder + '/' + blockName + '.twig';

  fs.writeFileSync(file, fileContent.replace(/COMPONENT/g, blockName));
  console.log('Twig: ' + file);
}

function writeDefaultStyle(blockName, fileFolder, gutenbergFolder) {
  const fileContent = fs.readFileSync(TEMPLATES.scss, 'utf8');
  const file = fileFolder + '/' + blockName + '.scss';

  fs.writeFileSync(file, fileContent.replace(/COMPONENT/g, blockName));
  console.log('SCSS: ' + file);

  // Load gutenberg styles file
  const gutenbergStylesFile = gutenbergFolder + '/gutenberg.scss';
  let gutenbergStyles = fs.readFileSync(gutenbergStylesFile);
  gutenbergStyles += '\r\n';
  gutenbergStyles += '@import "blocks/' + blockName + '/public/' + blockName + '.scss";';
  fs.writeFileSync(gutenbergStylesFile, gutenbergStyles);
  console.log('gutenberg.scss updated...');
}


