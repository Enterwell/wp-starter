/**
 * External dependencies
 */
import { isEmpty, get } from 'lodash';

/**
 * WordPress dependencies
 */
import { controls } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { apiFetch } from './controls';
import {
	path,
	receiveGroup,
	receiveGroupSettings,
	receiveMatchables,
	processItem,
	groupNotFound,
} from './actions';

export const getGroup = {
	*fulfill( id ) {
		try {
			const group = yield apiFetch( {
				path: `${ path }/${ id }?_embed=1`,
			} );
			yield receiveGroup( group );
			yield* processItem( group );
		} catch ( e ) {
			yield groupNotFound( id );
		}
	},
	isFulfilled( state, id ) {
		return !! state.byId[ id ];
	},
};

export const getMatchables = {
	*fulfill() {
		const matchables = yield apiFetch( {
			path: '/ithemes-security/v1/user-matchables?_embed=1',
		} );

		for ( const matchable of matchables ) {
			const group = get( matchable, [ '_embedded', 'self', 0 ] );
			const settings = get( matchable, [
				'_embedded',
				'ithemes-security:user-matchable-settings',
				0,
			] );

			if ( group ) {
				yield receiveGroup( group );
			}

			if ( settings ) {
				yield receiveGroupSettings( matchable.id, settings );
			}
		}

		yield receiveMatchables( matchables );
	},
	isFulfilled( state ) {
		return ! isEmpty( state.matchablesById );
	},
};

export const getGroupSettings = {
	*fulfill( id ) {
		try {
			const settings = yield apiFetch( {
				path: `ithemes-security/v1/user-matchable-settings/${ id }`,
			} );
			yield receiveGroupSettings( id, settings );
		} catch ( e ) {
			yield groupNotFound( id );
		}
	},
	isFulfilled( state, id ) {
		return !! state.settings[ id ];
	},
};

export const getGroupsBySetting = {
	*fulfill() {
		yield controls.resolveSelect(
			'ithemes-security/user-groups',
			'getMatchables'
		);
	},
	isFulfilled( state ) {
		return ! isEmpty( state.matchablesById );
	},
};
