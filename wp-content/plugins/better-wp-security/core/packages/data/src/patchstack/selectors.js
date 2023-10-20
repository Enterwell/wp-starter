/**
 * External dependencies
 */
import createSelector from 'rememo';
import { filter, find, get } from 'lodash';

/**
 * WordPress dependencies
 */
import { createRegistrySelector } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME } from './constant';

export const getPatchstackVulnerabilities = createRegistrySelector( ( select ) => () =>
	select( STORE_NAME ).getQueryResults( 'main' )
);

export function getPatchstackVulnerability( state, id ) {
	return state.byId[ id ];
}

export const getQueryResults = createSelector(
	( state, queryId ) => {
		const ids = get( state, [ 'queries', queryId, 'ids' ], [] );
		const byId = state.byId;

		const length = ids.length;
		const items = new Array( length );
		let index = -1;

		while ( ++index < length ) {
			const entry = byId[ ids[ index ] ];

			if ( entry ) {
				items[ index ] = entry.item;
			}
		}

		return items;
	},
	( state, queryId ) => [ state.queries[ queryId ], state.byId ]
);

export function getQueryHeaderLink( state, queryId, rel ) {
	return find( get( state, [ 'queries', queryId, 'links' ], [] ), { rel: [ rel ] } );
}

export function getQueryHeaderLinks( state, queryId, rel ) {
	return filter( get( state, [ 'queries', queryId, 'links' ], [] ), { rel: [ rel ] } );
}

export function getQueryHeader( state, queryId, header ) {
	return get( state, [ 'queries', queryId, 'headers', header ] );
}

export function getQueryParams( state, queryId ) {
	return get( state, [ 'queryParams', queryId ] );
}

export function queryHasNextPage( state, queryId ) {
	return getQueryHeaderLink( state, queryId, 'next' );
}

export function queryHasPrevPage( state, queryId ) {
	return !! getQueryHeaderLink( state, queryId, 'prev' );
}

export function getLastFetchError( state, queryId ) {
	return get( state, [ 'errors', queryId ] );
}

export function isQuerying( state, queryId ) {
	return state.querying.includes( queryId );
}
