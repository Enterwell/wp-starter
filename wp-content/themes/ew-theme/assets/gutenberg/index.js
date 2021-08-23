/**
 * Dynamically locate, load & register all Editor Blocks & Plugins.
 */
import {registerBlockType, unregisterBlockType} from '@wordpress/blocks';
import {registerPlugin, unregisterPlugin} from '@wordpress/plugins';
import {select, dispatch, registerStore, unregisterStore} from '@wordpress/data';
import BlockRegistrationHelper from './helpers/BlockRegistrationHelper';

/**
 * No-op function for use as a default argument value.
 *
 * @returns null
 */
const noop = () => null;

/**
 * Require a set of modules and configure them for hot module replacement.
 *
 * The first argument should be a function returning a `require.context()`
 * call. All modules loaded from this context are cached, and on each rebuild
 * the incoming updated modules are checked against the cache. Updated modules
 * which already exist in the cache are unregistered with the provided function,
 * then any incoming (new or updated) modules will be registered.
 *
 * @param {Function} getContext Execute and return a `require.context()` call.
 * @param {Function} register   Function to register accepted modules.
 * @param {Function} unregister Function to unregister replaced modules.
 * @param {Function} [before]   Function to run before updating moules.
 * @param {Function} [after]    Function to run after updating moules.
 */
const autoload = ({
                    getContext,
                    register,
                    unregister,
                    before = noop,
                    after = noop,
                  }) => {
  const cache = {};
  const loadModules = () => {
    before();
    const context = getContext();
    const changedNames = [];
    context.keys().forEach(key => {
      const module = context(key);
      if (module === cache[key]) {
        // Module unchanged: no further action needed.
        return;
      }
      if (cache[key]) {
        // Module changed, and prior copy detected: unregister old module.
        unregister(cache[key]);
      }
      // Register new module and update cache.
      register(module);
      changedNames.push(module.name);
      cache[key] = module;
    });

    after(changedNames);

    // Return the context for HMR initialization.
    return context;
  };


  const context = loadModules();

  if (module.hot) {
    module.hot.accept(context.id, loadModules);
  }
};

/**
 * Tries to get webpack module from context for given key.
 * @param moduleKey
 * @param context
 * @return {*}
 */
function getModule(moduleKey, context){
  try{
    return context(moduleKey);
  }catch(e){
    console.warn(`[BLOCK_REGISTRATION_FAILED] No module found for path: ${moduleKey}.`);
    return null;
  }
}

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

      register({ name, options });
      changedNames.push(name);
      cache[moduleKey] = { name, options };
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
const storeSelectedBlock = () => {
  selectedBlockId = select('core/editor').getSelectedBlockClientId();
  dispatch('core/editor').clearSelectedBlock();
};
const refreshAllBlocks = (changedNames = []) => {
  // Refresh all blocks by iteratively selecting each one.
  select('core/editor').getBlocks().forEach(({ name, clientId }) => {
    if (changedNames.includes(name)) {
      dispatch('core/editor').selectBlock(clientId);
    }
  });
  // Reselect whatever was selected in the beginning.
  if (selectedBlockId) {
    dispatch('core/editor').selectBlock(selectedBlockId);
  } else {
    dispatch('core/editor').clearSelectedBlock();
  }
  selectedBlockId = null;
};

// Load all block index files.
manifestAutoload({
  getContext: () => require.context('./blocks', true, /\.js$/),
  getManifestContext: () => require.context('./blocks', true, /manifest\.json$/),
  register: ({ name, options }) => {
    registerBlockType(name, options);
  },
  unregister: ({ name }) => unregisterBlockType(name),
  before: storeSelectedBlock,
  after: refreshAllBlocks,
});

// Load all stores
// autoload({
//   getContext: () => require.context('./stores', true, /\.js$/),
//   register: ({ name, options }) => registerStore(name, options),
//   unregister: noop,
//   before: storeSelectedBlock,
//   after: refreshAllBlocks,
// });

// Load all plugin files.
// autoload( {
//   getContext: () => require.context( './plugins', true, /index\.js$/ ),
//   register: ( { name, options } ) => registerPlugin( name, options ),
//   unregister: ( { name } ) => unregisterPlugin( name ),
// } );
