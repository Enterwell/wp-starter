/**
 * External dependencies
 */
import createSelector from 'rememo';
import { find, get, filter, castArray } from 'lodash';

/**
 * WordPress dependencies
 */
import { createRegistrySelector } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './constant';

/**
 * Gets the list of all logs.
 *
 * @return {Array<Object>} The list of logs.
 */

export const getLogs = createRegistrySelector( ( select ) => () =>
	select( STORE_NAME ).getQueryResults( 'main' )
);

/**
 * Gets the items returned by a query.
 *
 * @param {Object} state   State object.
 * @param {string} queryId Query id.
 * @return {Array<Object>}
 */
export const getQueryResults = createSelector(
	( state, queryId ) => {
		const selves = get( state, [ 'queries', queryId, 'selves' ], [] );
		const bySelf = state?.bySelf;

		const length = selves.length;
		const items = new Array( length );
		let index = -1;

		while ( ++index < length ) {
			const entry = bySelf[ selves[ index ] ];

			if ( entry ) {
				items[ index ] = entry.item;
			}
		}

		return items;
	},
	( state, queryId ) => [ state?.queries[ queryId ], state?.bySelf ]
);

/**
 * Gets the link header from a query result.
 *
 * @param {Object} state   State object.
 * @param {string} queryId Query id.
 * @param {string} rel     Rel to search for.
 * @return {{link: string, rel: string}} Link object or undefined if not found.
 */
export function getQueryHeaderLink( state, queryId, rel ) {
	return find( get( state, [ 'queries', queryId, 'links' ], [] ), { rel: castArray( rel ) } );
}

/**
 * Gets the link headers from a query result.
 *
 * @param {Object} state   State object.
 * @param {string} queryId Query id.
 * @param {string} rel     Rel to search for.
 * @return {Array<{link: string, rel: string}>} Link object or undefined if not found.
 */
export function getQueryHeaderLinks( state, queryId, rel ) {
	return filter( get( state, [ 'queries', queryId, 'links' ], [] ), { rel: castArray( rel ) } );
}

/**
 * Checks if a query has a previous page of results.
 *
 * @param {Object} state   Application state.
 * @param {string} queryId The query id to check.
 * @return {boolean} True if has previous page.
 */
export function queryHasPrevPage( state, queryId ) {
	return !! getQueryHeaderLink( state, queryId, 'prev' );
}

/**
 * Checks if a query has another page of results.
 *
 * @param {Object} state   Application state.
 * @param {string} queryId The query id to check.
 * @return {boolean} True if has next page.
 */
export function queryHasNextPage( state, queryId ) {
	return !! getQueryHeaderLink( state, queryId, 'next' );
}

/**
 * Get a response header from a query.
 *
 * @param {Object} state   State object.
 * @param {string} queryId Query id.
 * @param {string} header  Normalized header name.
 * @return {string|undefined} The header value, or undefined if it does not exist.
 */
export function getQueryHeader( state, queryId, header ) {
	return get( state, [ 'queries', queryId, 'headers', header ] );
}

/**
 * Gets the query parameters for a query.
 *
 * @param {Object} state   State object.
 * @param {string} queryId Query id.
 * @return {Object|undefined} The parameters, if any.
 */
export function getQueryParams( state, queryId ) {
	return get( state, [ 'queryParams', queryId ] );
}

/**
 * Checks if a query is in progress.
 *
 * @param {Object} state   Store data.
 * @param {string} queryId The query id.
 * @return {boolean} True if querying.
 */
export function isQuerying( state, queryId ) {
	return state.querying.includes( queryId );
}

/**
 * Gets a log by its self link.
 *
 * @param {Object} state Store data.
 * @param {string} self  Self link.
 * @return {Object|undefined} The log data.
 */
export function getLog( state, self ) {
	return state.bySelf[ self ]?.item;
}

/**
 * Gets a log by its id.
 *
 * @param {Object} state Store data.
 * @param {string} id    The log id.
 * @return {Object|undefined} The log data.
 */
export function getLogById( state, id ) {
	return getLog( state, state.selfById[ id ] );
}
