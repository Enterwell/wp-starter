/**
 * External dependencies
 */
import createSelector from 'rememo';
import { find, get, filter, castArray } from 'lodash';

/**
 * WordPres dependencies
 */
import { createRegistrySelector } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { getSelf } from '@ithemes/security-utils';
import { STORE_NAME } from './constant';

/**
 * Gets the list of all vulnerabilities.
 *
 * @return {Array<Object>} The list of vulnerabilities.
 */
export const getFirewallRules = createRegistrySelector( ( select ) => () =>
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
		const selves = get( state, [ 'query', 'queries', queryId, 'selves' ], [] );
		const bySelf = state.query.bySelf;

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
	( state, queryId ) => [ state.query.queries[ queryId ], state.query.bySelf ]
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
	return find( get( state, [ 'query', 'queries', queryId, 'links' ], [] ), { rel: castArray( rel ) } );
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
	return filter( get( state, [ 'query', 'queries', queryId, 'links' ], [] ), { rel: castArray( rel ) } );
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
	return get( state, [ 'query', 'queries', queryId, 'headers', header ] );
}

/**
 * Gets the query parameters for a query.
 *
 * @param {Object} state   State object.
 * @param {string} queryId Query id.
 * @return {Object|undefined} The parameters, if any.
 */
export function getQueryParams( state, queryId ) {
	return get( state, [ 'query', 'queryParams', queryId ] );
}

/**
 * Gets an item by its self link.
 *
 * @param {Object} state Store data.
 * @param {string} self  Self link.
 * @return {Object|undefined} The item data.
 */
export function getItem( state, self ) {
	return state.query.bySelf[ self ]?.item;
}

/**
 * Gets an item by its id.
 *
 * @param {Object} state Store data.
 * @param {string} id    The item id.
 * @return {Object|undefined} The item data.
 */
export function getItemById( state, id ) {
	return getItem( state, state.query.selfById[ id ] );
}

/**
 * Checks if the query is in progress.
 *
 * @param {Object} state   Application state.
 * @param {string} queryId The query id to check.
 * @return {boolean} True if querying.
 */
export function isQuerying( state, queryId ) {
	return state.query.querying.includes( queryId );
}

/**
 * Gets an item with edits applied.
 *
 * @type {(function(string): Object|undefined)}
 */
export const getEditedItem = createSelector(
	( state, self ) => state.query.bySelf[ self ]?.item && ( {
		...state.query.bySelf[ self ].item,
		...( state.edits.bySelf[ self ] || {} ),
	} ),
	( state, self ) => [ state.edits.bySelf[ self ], state.query.bySelf[ self ] ]
);

/**
 * Checks if an item has any pending edits.
 *
 * @param {Object} state Application state.
 * @param {string} self  Item self link.
 * @return {boolean} True when edits exist.
 */
export function isDirty( state, self ) {
	return state.edits.bySelf[ self ] !== undefined;
}

/**
 * Checks if an item is being saved.
 *
 * @param {Object}        state      Application state.
 * @param {string|Object} itemOrSelf Item self link.
 * @return {boolean} True if saving.
 */
export function isSaving( state, itemOrSelf ) {
	const self = typeof itemOrSelf === 'string' ? itemOrSelf : getSelf( itemOrSelf );
	return state.saving.selves.includes( self );
}

/**
 * Checks if an item is being deleted.
 *
 * @param {Object}        state      Application state.
 * @param {string|Object} itemOrSelf Item self link.
 * @return {boolean} True if deleting.
 */
export function isDeleting( state, itemOrSelf ) {
	const self = typeof itemOrSelf === 'string' ? itemOrSelf : getSelf( itemOrSelf );
	return state.deleting.selves.includes( self );
}

/**
 * Gets the last error encountered when saving.
 *
 * @param {Object}        state      Application state.
 * @param {string|Object} itemOrSelf Item self link.
 * @return {Object|undefined} Error response if any.
 */
export function getLastSaveError( state, itemOrSelf ) {
	const self = typeof itemOrSelf === 'string' ? itemOrSelf : getSelf( itemOrSelf );
	return state.saving.errors[ self ];
}

/**
 * Gets the last error encountered when deleting.
 *
 * @param {Object}        state      Application state.
 * @param {string|Object} itemOrSelf Item self link.
 * @return {Object|undefined} Error response if any.
 */
export function getLastDeleteError( state, itemOrSelf ) {
	const self = typeof itemOrSelf === 'string' ? itemOrSelf : getSelf( itemOrSelf );
	return state.deleting.errors[ self ];
}
