/**
 * WordPress dependencies
 */
import {
	dispatch as dispatchData,
	select as selectData,
} from '@wordpress/data';
import { default as triggerApiFetch } from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import { responseToError } from '@ithemes/security-utils';

/**
 * Trigger an API Fetch request.
 *
 * @param {Object} request API Fetch Request Object.
 * @return {Object} control descriptor.
 */
export function apiFetch( request ) {
	return {
		type: 'API_FETCH',
		request,
	};
}

/**
 * Calls a selector using the current state.
 *
 * @param {string} storeKey     Store key.
 * @param {string} selectorName Selector name.
 * @param {Array}  args         Selector arguments.
 *
 * @return {Object} control descriptor.
 */
export function select( storeKey, selectorName, ...args ) {
	return {
		type: 'SELECT',
		storeKey,
		selectorName,
		args,
	};
}

/**
 * Dispatches a control action for triggering a registry dispatch.
 *
 * @param {string} storeKey   The key for the store the action belongs to
 * @param {string} actionName The name of the action to dispatch
 * @param {Array}  args       Arguments for the dispatch action.
 *
 * @example
 * ```js
 * import { dispatch } from '@wordpress/data-controls';
 *
 * // Action generator using dispatch
 * export function* myAction() {
 * 	yield dispatch( 'core/edit-post', 'togglePublishSidebar' );
 * 	// do some other things.
 * }
 * ```
 *
 * @return {Object}  The control descriptor.
 */
export function dispatch( storeKey, actionName, ...args ) {
	return {
		type: 'DISPATCH',
		storeKey,
		actionName,
		args,
	};
}

const controls = {
	API_FETCH( { request } ) {
		return triggerApiFetch( request ).catch( responseToError );
	},
	SELECT( { storeKey, selectorName, args } ) {
		return selectData( storeKey )[ selectorName ]( ...args );
	},
	DISPATCH( { storeKey, actionName, args } ) {
		return dispatchData( storeKey )[ actionName ]( ...args );
	},
};

export default controls;
