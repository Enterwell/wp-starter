/**
 * External dependencies
 */
import { uniqueId, chunk, times, constant } from 'lodash';

/**
 * WordPress dependencies
 */
import {
	select as selectData,
	dispatch as dispatchData,
	subscribe,
	createRegistryControl,
} from '@wordpress/data';
import { default as triggerApiFetch } from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import { responseToError, Result, WPError } from '@ithemes/security-utils';
import { CORE_STORE_NAME } from './';

/**
 * Utility for returning a promise that handles a selector with a resolver.
 *
 * @param {Object} options
 * @param {string} options.storeKey     The store the selector belongs to
 * @param {string} options.selectorName The selector name
 * @param {Array}  options.args         The arguments fed to the selector
 *
 * @return {Promise}  A promise for resolving the given selector.
 */
const resolveSelect = ( { storeKey, selectorName, args } ) => {
	return new Promise( ( resolve ) => {
		const hasFinished = () =>
			selectData( 'core/data' ).hasFinishedResolution(
				storeKey,
				selectorName,
				args
			);
		const getResult = () =>
			selectData( storeKey )[ selectorName ].apply( null, args );

		// trigger the selector (to trigger the resolver)
		const result = getResult();

		if ( hasFinished() ) {
			return resolve( result );
		}

		const unsubscribe = subscribe( () => {
			if ( hasFinished() ) {
				unsubscribe();
				resolve( getResult() );
			}
		} );
	} );
};

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
 * Triggers an API fetch request converting the response to a Result object.
 *
 * @param {Object} request API Fetch Request Object.
 * @return {{request, type: string}} Control descriptor.
 */
export function apiFetchResult( request ) {
	return {
		type: 'API_FETCH_RESULT',
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
 * @param {...*}   args       Arguments for the dispatch action.
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
 * Performs a native fetch request.
 *
 * @param {window.RequestInfo} request
 * @param {window.RequestInit} init
 * @return {{request, type: string}} The control descriptor.
 */
export function fetch( request, init ) {
	return {
		type: 'FETCH',
		request,
		init,
	};
}

/**
 * Parses the fetch response.
 *
 * @param {Response} response The response object from apiFetch.
 * @return {{response: *, type: string}} Data control.
 */
export function parseFetchResponse( response ) {
	return {
		type: 'PARSE_FETCH_RESPONSE',
		response,
	};
}

export function awaitPromise( promise, delay ) {
	return {
		type: 'AWAIT_PROMISE',
		promise,
		delay,
	};
}

/**
 * Parse the fetch response into an object with data and headers.
 *
 * @param {Response} response The response object from apiFetch.
 * @return {Promise<*>} Parsed response object.
 */
async function PARSE_FETCH_RESPONSE( { response } ) {
	return await response.json();
}

/**
 * Updates a module's settings.
 *
 * @param {string} module   The module id.
 * @param {Object} settings The settings to update.
 * @return {{settings, module, type: string}} The control descriptor.
 */
export function updateSettings( module, settings ) {
	return {
		type: 'UPDATE_SETTINGS',
		module,
		settings,
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

export function apiFetchBatch( batch ) {
	return {
		type: 'API_FETCH_BATCH',
		batch,
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
	API_FETCH( { request } ) {
		return triggerApiFetch( request ).catch( responseToError );
	},
	API_FETCH_RESULT( { request } ) {
		return triggerApiFetch( { ...request, parse: false } )
			.then( Result.fromResponse )
			.catch( responseToError )
			.catch( ( error ) =>
				error.getResponse
					? Result.fromResponse( error.getResponse() )
					: new Result(
						Result.ERROR,
						new WPError( 'unknown_error', 'Unknown error' )
					)
			);
	},
	SELECT( { storeKey, selectorName, args } ) {
		const selector = selectData( storeKey )[ selectorName ];

		if ( selector.hasResolver ) {
			return resolveSelect( { storeKey, selectorName, args } );
		}

		return selector( ...args );
	},
	DISPATCH( { storeKey, actionName, args } ) {
		return dispatchData( storeKey )[ actionName ]( ...args );
	},
	PARSE_FETCH_RESPONSE,
	FETCH( { request, init } ) {
		return window.fetch( request, init );
	},
	UPDATE_SETTINGS: createRegistryControl(
		( registry ) => ( { module, settings } ) =>
			registry
				.dispatch( 'ithemes-security/modules' )
				.updateSettings( module, settings )
	),
	CREATE_NOTICE( { status, content, options } ) {
		if ( options.autoDismiss ) {
			options.id = options.id || uniqueId( 'itsec-auto-dismiss-' );
			setTimeout(
				() =>
					dispatchData( 'core/notices' ).removeNotice(
						options.id,
						options.context
					),
				options.autoDismiss
			);
		}

		dispatchData( 'core/notices' ).createNotice( status, content, options );
	},
	API_FETCH_BATCH: createRegistryControl(
		( registry ) => async ( { batch } ) => {
			const maxItems = await registry
				.resolveSelect( CORE_STORE_NAME )
				.getBatchMaxItems();
			const chunks = chunk( batch, maxItems || 25 );
			const errors = [];
			const responses = [];

			if ( ! chunks.length ) {
				return [];
			}

			for ( const requests of chunks ) {
				try {
					const response = await controls.API_FETCH( {
						request: {
							path: '/batch/v1',
							method: 'POST',
							data: { requests },
						},
					} );
					responses.push( ...response.responses );
				} catch ( e ) {
					errors.push( e );
					responses.push(
						...times(
							requests.length,
							constant( {
								body: e,
								status: 500,
								headers: {},
							} )
						)
					);
				}
			}

			if ( errors.length === chunks.length ) {
				throw errors[ 0 ];
			}

			return responses;
		}
	),
};

export default controls;
