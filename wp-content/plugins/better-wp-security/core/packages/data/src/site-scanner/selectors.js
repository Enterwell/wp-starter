/**
 * External dependencies
 */
import createSelector from 'rememo';
import { find, get, filter } from 'lodash';

/**
 * WordPres dependencies
 */
import { createRegistrySelector } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './constant';

/**
 * Gets the list of all scans.
 *
 * @return {Array<Object>} The list of scans.
 */
export const getScans = createRegistrySelector( ( select ) => () =>
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
		const bySelf = state.bySelf;

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
	( state, queryId ) => [ state.queries[ queryId ], state.bySelf ]
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
	return find( get( state, [ 'queries', queryId, 'links' ], [] ), { rel } );
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
	return filter( get( state, [ 'queries', queryId, 'links' ], [] ), { rel } );
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
 * Gets a scan by its self link.
 *
 * @param {Object} state Store data.
 * @param {string} self  Self link.
 * @return {Object|undefined} The scam data.
 */
export function getScan( state, self ) {
	return state.bySelf[ self ]?.item;
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
 * Checks if a site is being scanned.
 *
 * @param {Object} state    Application state.
 * @param {number} [siteId] The site ID.
 * @return {boolean} True if scanning.
 */
export function isScanning( state, siteId = 0 ) {
	return state.scanning === siteId;
}
