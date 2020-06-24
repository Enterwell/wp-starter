/**
 * External dependencies
 */
import { isEmpty, get } from 'lodash';

/**
 * Internal dependencies
 */
import { apiFetch } from './controls';
import { path, receiveGroup, receiveGroupSettings, receiveMatchables, processItem } from './actions';

export const getGroup = {
	*fulfill( id ) {
		const group = yield apiFetch( { path: `${ path }/${ id }?_embed=1` } );
		yield receiveGroup( group );
		yield* processItem( group );
	},
	isFulfilled( state, id ) {
		return state.byId.hasOwnProperty( id );
	},
};

export const getMatchables = {
	*fulfill() {
		const matchables = yield apiFetch( { path: '/ithemes-security/v1/user-matchables?_embed=1' } );
		yield receiveMatchables( matchables );

		for ( const matchable of matchables ) {
			const group = get( matchable, [ '_embedded', 'self', 0 ] );
			const settings = get( matchable, [ '_embedded', 'ithemes-security:user-matchable-settings', 0 ] );

			if ( group ) {
				yield receiveGroup( group );
			}

			if ( settings ) {
				yield receiveGroupSettings( matchable.id, settings );
			}
		}
	},
	isFulfilled( state ) {
		return ! isEmpty( state.matchablesById );
	},
};

export const getGroupSettings = {
	*fulfill( id ) {
		const settings = yield apiFetch( { path: `ithemes-security/v1/user-matchable-settings/${ id }` } );
		yield receiveGroupSettings( id, settings );
	},
	isFulfilled( state, id ) {
		return state.settings.hasOwnProperty( id );
	},
};
