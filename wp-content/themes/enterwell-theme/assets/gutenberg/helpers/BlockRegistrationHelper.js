import projectManifest from '../manifest.json';
import {InnerBlocks} from '@wordpress/editor';
import {createElement} from '@wordpress/element';

class BlockRegistrationHelper {
  static getBlockName(manifest) {
    const { projectNamespace } = projectManifest;
    const { blockName } = manifest;
    if (!blockName) throw new Error('Required field "blockName" is missing from the manifest.');

    return [projectNamespace, blockName].join('/');
  }

  static getBlockOptions(editComponent, manifest, customOptions = {}) {
    const {
      blockName,
      title,
      description,
      category,
      keywords,
      supports,
      parent,
      hasInnerBlocks,
      styles
    } = manifest;

    return {
      blockName,
      title,
      description,
      category,
      keywords,
      supports,
      parent,
      styles,
      save: (hasInnerBlocks ? () => createElement(InnerBlocks.Content) : () => null),
      ...customOptions,
      edit: editComponent,
    };
  }
}

export default BlockRegistrationHelper;