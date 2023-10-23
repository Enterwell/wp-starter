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
import { STORE_NAME } from './constant';

export const getUsers = createRegistrySelector( ( select ) => () =>
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
 * Get user from state
 *
 * @param {Object} state Application State.
 * @param {Object} self  User
 * @return {Object} Returns User from state
 */
export function getUser( state, self ) {
	return state.bySelf[ self ]?.item;
}

/**
 * Lookup a user by their id
 *
 * @param {Object} state Application State.
 * @param {number} id    The User's id
 * @return {Object|undefined} Matched User or nothing
 */
export function getUserById( state, id ) {
	return getUser( state, state.selfById[ id ] );
}

/**
 * Get the User Selection Type from state
 *
 * @param {Object} state Application State.
 * @return {string} window | all
 */
export function getUserSelectionType( state ) {
	return state.userSelection;
}

/**
 * Checks whether a quick action is running.
 *
 * @param {Object} state       Application State.
 * @param {string} idOrQueryId The queryId when applying actions to a query
 *                             or the id used when applying actions to a list of users.
 * @return {boolean} True if the action is being applied.
 */
export function isApplyingQuickActions( state, idOrQueryId ) {
	return state.actions.includes( idOrQueryId );
}

/**
 * Returns the Quick Action error by id
 *
 * @param {Object} state       Application State.
 * @param {string} idOrQueryId The queryId when applying actions to a query
 *                             or the id used when applying actions to a list of users.
 * @return {Object|undefined} The error, if it exists
 */
export function getQuickActionsError( state, idOrQueryId ) {
	return state.actionErrors[ idOrQueryId ];
}

/**
 * Get the selectedUsers from state
 *
 * @param {Object} state Application state
 * @return {Array<number>} Array of selected users objects
 */
export function getCurrentlySelectedUsers( state ) {
	return state.selectedUsers;
}

/**
 * Get the state of the select checkbox
 *
 * @param {Object} state Application state
 * @return {string} State string 'checked' || 'indeterminate' || 'unchecked'
 */
export function getSelectAllState( state ) {
	const queriedUsers = getQueryResults( state, 'main' );
	if ( state.selectedUsers.length === queriedUsers.length ) {
		return 'checked';
	} else if ( state.selectedUsers.length > 0 ) {
		return 'indeterminate';
	}

	return 'unchecked';
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
 * Check if user is selected
 *
 * @param {Object } state  Application state
 * @param {number}  userId The id of the User
 * @return {boolean} True if selected
 */
export function isUserSelected( state, userId ) {
	return state.selectedUsers.includes( userId );
}

export function getQuickEditState( state ) {
	return state.quickEditState;
}

export function getQuickEditActions( state ) {
	return state.actions;
}
