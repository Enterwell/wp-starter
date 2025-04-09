/**
 * External dependencies
 */
import createSelector from 'rememo';
import { find, get, isObject, filter } from 'lodash';

/**
 * WordPress dependencies
 */
import { createRegistrySelector } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { getSelf } from '@ithemes/security-utils';

/**
 * Gets the list of all bans.
 *
 * @return {Array<Object>} The list of bans.
 */
export const getBans = createRegistrySelector( ( select ) => () =>
	select( 'ithemes-security/bans' ).getQueryResults( 'main' )
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
 * Gets a ban by its self link.
 *
 * @param {Object} state Store data.
 * @param {string} self  Self link.
 * @return {Object|undefined} The ban data.
 */
export function getBan( state, self ) {
	return state.bySelf[ self ];
}

/**
 * Checks if the given ban is being updated.
 *
 * @param {Object} state     Store data.
 * @param {string} banOrSelf Ban object or self link.
 * @return {boolean} True if updating.
 */
export function isUpdating( state, banOrSelf ) {
	const self = isObject( banOrSelf ) ? getSelf( banOrSelf ) : banOrSelf;

	return state.updating.includes( self );
}

/**
 * Checks if the given ban is being deleted.
 *
 * @param {Object} state     Store data.
 * @param {string} banOrSelf Ban object or self link.
 * @return {boolean} True if deleting.
 */
export function isDeleting( state, banOrSelf ) {
	const self = isObject( banOrSelf ) ? getSelf( banOrSelf ) : banOrSelf;

	return state.deleting.includes( self );
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
