/**
 * External dependencies
 */
import createSelector from 'rememo';
import { find, get, filter, reduce, castArray } from 'lodash';

/**
 * WordPres dependencies
 */
import { createRegistrySelector } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './constant';
import { getSelf } from '@ithemes/security-utils';

/**
 * Gets the list of all vulnerabilities.
 *
 * @return {Array<Object>} The list of vulnerabilities.
 */
export const getVulnerabilities = createRegistrySelector( ( select ) => () =>
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
 * Gets a vulnerability by its self link.
 *
 * @param {Object} state Store data.
 * @param {string} self  Self link.
 * @return {Object|undefined} The vulnerability data.
 */
export function getVulnerability( state, self ) {
	return state.bySelf[ self ]?.item;
}

/**
 * Gets a vulnerability by its id.
 *
 * @param {Object} state Store data.
 * @param {string} id    The vulnerability id.
 * @return {Object|undefined} The vulnerability data.
 */
export function getVulnerabilityById( state, id ) {
	return getVulnerability( state, state.selfById[ id ] );
}

/**
 * Gets the available actions that can be taken for a vulnerability.
 *
 * @param {Object}        state               Application state.
 * @param {Object|string} vulnerabilityOrSelf Vulnerability item or self link.
 * @return {{rel: string, title: string, isDestructive: boolean}[]} List of actionsl
 */
export function getVulnerabilityActions( state, vulnerabilityOrSelf ) {
	const vulnerability = typeof vulnerabilityOrSelf === 'string'
		? getVulnerability( state, vulnerabilityOrSelf )
		: vulnerabilityOrSelf;

	if ( ! vulnerability ) {
		return [];
	}

	return reduce( vulnerability._links, ( acc, links, rel ) => {
		return links.reduce( ( relAcc, link ) => {
			if ( ! link.title ) {
				return relAcc;
			}

			relAcc.push( {
				rel,
				title: link.title,
				isDestructive: link.isDestructive || false,
			} );

			return relAcc;
		}, acc );
	}, [] );
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
 * Checks if a vulnerability is being muted.
 *
 * @param {Object}        state               Store state.
 * @param {Object|string} vulnerabilityOrSelf Vulnerability data or self link.
 * @return {boolean} True if muting.
 */
export function isMuting( state, vulnerabilityOrSelf ) {
	return isApplyingAction( state, vulnerabilityOrSelf, 'ithemes-security:mute-vulnerability' );
}

/**
 * Checks if a vulnerability is being fixed.
 *
 * @param {Object}        state               Store state.
 * @param {Object|string} vulnerabilityOrSelf Vulnerability data or self link.
 * @return {boolean} True if fixing.
 */
export function isFixing( state, vulnerabilityOrSelf ) {
	return isApplyingAction( state, vulnerabilityOrSelf, 'ithemes-security:fix-vulnerability' );
}

/**
 * Checks if a vulnerable software is being deactivated.
 *
 * @param {Object}        state               Store state.
 * @param {Object|string} vulnerabilityOrSelf Vulnerability data or self link.
 * @return {boolean} True if deactivating.
 */
export function isDeactivatingSoftware( state, vulnerabilityOrSelf ) {
	return isApplyingAction( state, vulnerabilityOrSelf, 'ithemes-security:deactivate-vulnerable-software' );
}

/**
 * Checks if a vulnerability action is being applied.
 *
 * @param {Object}        state               Store state.
 * @param {Object|string} vulnerabilityOrSelf Vulnerability data or self link.
 * @param {string}        rel                 Link relation.
 * @return {boolean} True if in progress.
 */
export function isApplyingAction( state, vulnerabilityOrSelf, rel ) {
	const self = typeof vulnerabilityOrSelf === 'string'
		? vulnerabilityOrSelf
		: getSelf( vulnerabilityOrSelf );

	return state.actions.includes( `${ rel }:${ self }` );
}
