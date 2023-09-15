/**
 * External dependencies
 */
import { get } from 'lodash';

/**
 * WordPress dependencies
 */
import { controls } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { apiFetch, select } from './controls';
import {
	receiveActors,
	receiveActorTypes,
	receiveBatchMaxItems,
	receiveCurrentUserId,
	receiveIndex,
	receiveSiteInfo,
	receiveUser,
} from './actions';

export function* getIndex() {
	const index = yield apiFetch( {
		path: '/ithemes-security/v1?context=help',
	} );
	yield receiveIndex( index );
}

export const getSchema = {
	*fulfill() {
		yield controls.resolveSelect( 'ithemes-security/core', 'getIndex' );
	},
	isFulfilled( state ) {
		return !! state.index;
	},
};

export const getRoles = {
	*fulfill() {
		yield controls.resolveSelect( 'ithemes-security/core', 'getIndex' );
	},
	isFulfilled( state ) {
		return !! state.index;
	},
};

export const getRequirementsInfo = {
	*fulfill() {
		yield controls.resolveSelect( 'ithemes-security/core', 'getIndex' );
	},
	isFulfilled( state ) {
		return !! state.index;
	},
};

export const getUser = {
	*fulfill( id ) {
		const currentUserId = yield select( 'ithemes-security/core', 'getCurrentUserId' );
		const user = yield apiFetch( {
			path: `/wp/v2/users/${ id === currentUserId ? 'me' : id }?context=edit`,
		} );

		yield receiveUser( user );
	},
	isFulfilled( state, userId ) {
		return !! state.users.byId[ userId ];
	},
};

export const getCurrentUser = {
	*fulfill() {
		const user = yield apiFetch( {
			path: '/wp/v2/users/me?context=edit',
		} );

		yield receiveUser( user );
		yield receiveCurrentUserId( user.id );
	},
	isFulfilled( state ) {
		return (
			state.users.currentId && state.users.byId[ state.users.currentId ]
		);
	},
};

export const getActorTypes = {
	*fulfill() {
		const response = yield apiFetch( {
			path: '/ithemes-security/v1/actors?_embed=1',
		} );

		const types = [];

		for ( const type of response ) {
			const actors = get( type, [ '_embedded', 'wp:items', 0 ], [] );

			yield receiveActors( type.slug, actors );
			types.push( { slug: type.slug, label: type.label } );
		}

		yield receiveActorTypes( types );
	},

	isFulfilled( state ) {
		return state.actors.types.length > 0;
	},
};

export const getActors = {
	*fulfill() {
		yield select( 'ithemes-security/core', 'getActorTypes' );
	},
	isFulfilled( state, type ) {
		return !! state.actors.byType[ type ];
	},
};

export const getSiteInfo = {
	*fulfill() {
		const response = yield apiFetch( {
			path: '/?_fields=name,description,url,home,multisite',
		} );
		yield receiveSiteInfo( response );
	},
	isFulfilled( state ) {
		return !! state.siteInfo;
	},
};

export function* getBatchMaxItems() {
	const response = yield apiFetch( {
		path: '/batch/v1',
		method: 'OPTIONS',
	} );
	yield receiveBatchMaxItems(
		response.endpoints[ 0 ].args.requests.maxItems
	);
}
