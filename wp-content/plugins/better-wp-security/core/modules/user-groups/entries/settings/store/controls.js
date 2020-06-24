/**
 * External dependencies
 */
import { uniqueId } from 'lodash';

/**
 * WordPress dependencies
 */
import { select as selectData, dispatch as dispatchData } from '@wordpress/data';
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
 * @param {string} storeKey Store key.
 * @param {string} selectorName Selector name.
 * @param {Array} args         Selector arguments.
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
 * @param {string} storeKey    The key for the store the action belongs to
 * @param {string} actionName  The name of the action to dispatch
 * @param {Array}  args        Arguments for the dispatch action.
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

/**
 * Yields action objects used in signalling that a notice is to be created.
 *
 * @see @wordpress/notices#createNotice()
 *
 * @param {?string}                status                Notice status.
 *                                                       Defaults to `info`.
 * @param {string}                 content               Notice message.
 * @param {?Object}                options               Notice options.
 * @param {?string}                options.context       Context under which to
 *                                                       group notice.
 * @param {?string}                options.id            Identifier for notice.
 *                                                       Automatically assigned
 *                                                       if not specified.
 * @param {?boolean}               options.isDismissible Whether the notice can
 *                                                       be dismissed by user.
 *                                                       Defaults to `true`.
 * @param {?number}                options.autoDismiss   Whether the notice should
 *                                                       by automatically dismissed
 *                                                       after x milliseconds.
 *                                                       Defaults to `false`.
 * @param {?string}                options.type          Notice type. Either 'default' or 'snackbar'.
 * @param {?Array<WPNoticeAction>} options.actions       User actions to be
 *                                                       presented with notice.
 *
 * @return {Object} control descriptor.
 */
export function createNotice( status = 'info', content, options = {} ) {
	return {
		type: 'CREATE_NOTICE',
		status,
		content,
		options: {
			context: 'ithemes-security',
			...options,
		},
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
	CREATE_NOTICE( { status, content, options } ) {
		if ( options.autoDismiss ) {
			options.id = options.id || uniqueId( 'itsec-auto-dismiss-' );
			setTimeout( () => dispatchData( 'core/notices' ).removeNotice( options.id, options.context ), options.autoDismiss );
		}

		dispatchData( 'core/notices' ).createNotice( status, content, options );
	},
};

export default controls;
