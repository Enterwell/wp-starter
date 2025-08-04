/**
 * Dynamically locate, load & register all Editor Blocks.
 */
import {registerBlockType, unregisterBlockType} from '@wordpress/blocks';
import {select, dispatch} from '@wordpress/data';
import BlockRegistrationHelper from './helpers/BlockRegistrationHelper';

/**
 * No-op function for use as a default argument value.
 *
 * @returns null
 */
const noop = () => null;

/**
 * Tries to get webpack module from context for given key.
 * @param moduleKey
 * @param context
 * @return {*}
 */
function getModule(moduleKey, context) {
	try {
		return context(moduleKey);
	} catch (e) {
		console.warn(`[BLOCK_REGISTRATION_FAILED] No module found for path: ${moduleKey}.`);
		return null;
	}
}

/**
 * Autoloads gutenberg blocks and their respective manifests and registers them
 *
 * @param getContext Execute and return a `require.context()` call.
 * @param getManifestContext Execute and return a `require.context()` call.
 * @param register Function to register accepted modules.
 * @param unregister Function to unregister replaced modules.
 * @param before Function to run before updating modules.
 * @param after Function to run before updating modules.
 */
function manifestAutoload({
														getContext,
														getManifestContext,
														register,
														unregister,
														before = noop,
														after = noop,
													}) {
	const cache = {};

	const loadModules = () => {
		before();

		// Get modules context
		const context = getContext();
		// Get manifests context
		const manifestContext = getManifestContext();

		// If no manifests found - return
		if (!manifestContext) {
			console.error('No manifestContext found for context.', context);
			return context;
		}

		const changedNames = [];

		manifestContext.keys().forEach(key => {
			const manifestModule = manifestContext(key);
			const moduleKey = key.replace('manifest.json', `admin/${manifestModule.blockName}.js`);
			const module = getModule(moduleKey, context);

			if (!module) {
				return;
			}

			if (module === cache[moduleKey]) {
				// Module unchanged: no further action needed.
				return;
			}
			if (cache[moduleKey]) {
				// Module changed, and prior copy detected: unregister old module.
				unregister(cache[moduleKey]);
			}
			// Register new module and update cache.
			const name = BlockRegistrationHelper.getBlockName(manifestModule);
			const options = BlockRegistrationHelper.getBlockOptions(module.default, manifestModule);

			if (!name) {
				console.error(`No block name for manifest!`, manifestModule);
				return;
			}

			if (!module.default) {
				console.warn(`Module is missing edit component default export [${name}].`);
				return;
			}

			register({name, options});
			changedNames.push(name);
			cache[moduleKey] = {name, options};
		});

		after(changedNames);

		// Return the context for HMR initialization.
		return context;
	};

	const context = loadModules();

	if (module.hot) {
		module.hot.accept(context.id, loadModules);
	}
}

// Maintain the selected block ID across HMR updates.
let selectedBlockId = null;

/**
 * Clears block selection abd saves currently selected block
 */
const storeSelectedBlock = () => {
	selectedBlockId = select('core/block-editor').getSelectedBlockClientId();
	dispatch('core/block-editor').clearSelectedBlock();
};

/**
 * Refreshes all blocks if provided in changedNames array
 * Selects previously selected one
 *
 * @param changedNames Array of changed blocks
 */
const refreshAllBlocks = (changedNames = []) => {
	// Refresh all blocks by iteratively selecting each one.
	select('core/block-editor').getBlocks().forEach(({name, clientId}) => {
		if (changedNames.includes(name)) {
			dispatch('core/block-editor').selectBlock(clientId);
		}
	});

	// Reselect whatever was selected in the beginning.
	if (selectedBlockId) {
		dispatch('core/block-editor').selectBlock(selectedBlockId);
	} else {
		dispatch('core/block-editor').clearSelectedBlock();
	}

	selectedBlockId = null;
};

// Load all blocks
manifestAutoload({
  getContext: () => require.context('./blocks', true, /\.js$/),
  getManifestContext: () => require.context('./blocks', true, /manifest\.json$/),
  register: ({name, options}) => {
    registerBlockType(name, options);
  },
  unregister: ({name}) => unregisterBlockType(name),
  before: storeSelectedBlock,
  after: refreshAllBlocks,
});
