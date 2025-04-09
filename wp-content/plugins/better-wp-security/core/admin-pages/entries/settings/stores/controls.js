/**
 * External dependencies
 */
import { uniqueId } from 'lodash';

/**
 * WordPress dependencies
 */
import { default as triggerApiFetch } from '@wordpress/api-fetch';
import { createRegistryControl } from '@wordpress/data';
import { doAction as doActionHook } from '@wordpress/hooks';

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
 * Yields action objects used in signalling that a notice is to be created.
 *
 * @see @wordpress/notices#createNotice()
 *
 * @param {?string}        status                Notice status.
 *                                               Defaults to `info`.
 * @param {string}         content               Notice message.
 * @param {?Object}        options               Notice options.
 * @param {?string}        options.context       Context under which to
 *                                               group notice.
 * @param {?string}        options.id            Identifier for notice.
 *                                               Automatically assigned
 *                                               if not specified.
 * @param {?boolean}       options.isDismissible Whether the notice can
 *                                               be dismissed by user.
 *                                               Defaults to `true`.
 * @param {?number}        options.autoDismiss   Whether the notice should
 *                                               by automatically dismissed
 *                                               after x milliseconds.
 *                                               Defaults to `false`.
 * @param {?string}        options.type          Notice type. Either 'default' or 'snackbar'.
 * @param {?Array<Object>} options.actions       User actions to be
 *                                               presented with notice.
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

export function awaitPromise( promise, delay ) {
	return {
		type: 'AWAIT_PROMISE',
		promise,
		delay,
	};
}

export function doAction( action, ...args ) {
	return {
		type: 'DO_ACTION',
		action,
		args,
	};
}

function timeout( ms ) {
	return new Promise( ( resolve ) => setTimeout( resolve, ms ) );
}

const controls = {
	AWAIT_PROMISE: ( { promise, delay } ) => {
		if ( delay ) {
			return Promise.all( [ promise, timeout( delay ) ] );
		}

		return promise;
	},
	DO_ACTION: createRegistryControl( ( registry ) => ( { action, args } ) => {
		doActionHook( `ithemes-security.${ action }`, registry, ...args );
	} ),
	API_FETCH( { request } ) {
		return triggerApiFetch( request ).catch( responseToError );
	},
	CREATE_NOTICE: createRegistryControl(
		( registry ) => ( { status, content, options } ) => {
			if ( options.autoDismiss ) {
				options.id = options.id || uniqueId( 'itsec-auto-dismiss-' );
				setTimeout(
					() =>
						registry
							.dispatch( 'core/notices' )
							.removeNotice( options.id, options.context ),
					options.autoDismiss
				);
			}

			registry
				.dispatch( 'core/notices' )
				.createNotice( status, content, options );
		}
	),
};

export default controls;
