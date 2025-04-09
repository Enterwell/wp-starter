/**
 * External dependencies
 */
import createSelector from 'rememo';
import { merge, cloneDeep, find } from 'lodash';

/**
 * WordPress dependencies
 */
import { createRegistrySelector } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { MODULES_STORE_NAME } from './';

/**
 * Get a WP User by its ID.
 *
 * @param {Object} state
 * @param {number} userId
 * @return {Object} User data.
 */
export const getUser = createSelector(
	( state, userId ) => state.users.optimisticEdits[ userId ]
		? merge( cloneDeep( state.users.byId[ userId ] ), state.users.optimisticEdits[ userId ] )
		: state.users.byId[ userId ],
	( state, userId ) => [ state.users.byId[ userId ], state.users.optimisticEdits[ userId ] ]
);

/**
 * Get the current user.
 *
 * @param {Object} state The store state.
 * @return {Object} The current user object.
 */
export function getCurrentUser( state ) {
	return getUser( state, getCurrentUserId( state ) );
}

/**
 * Get the current user id.
 *
 * @param {Object} state The store state.
 * @return {number} The current user id.
 */
export function getCurrentUserId( state ) {
	return state.users.currentId;
}

/**
 * Is the given user being updated.
 *
 * @param {Object} state  The store state.
 * @param {number} userId The user id to query.
 * @return {boolean} True if saving.
 */
export function isSavingUser( state, userId ) {
	return state.users.saving.includes( userId );
}

/**
 * Is the current user being updated.
 *
 * @param {Object} state The store state.
 * @return {boolean} True if saving.
 */
export function isSavingCurrentUser( state ) {
	return isSavingUser( state, state.users.currentId );
}

export function getIndex( state ) {
	return state.index;
}

/**
 * Get a schema from the root index.
 *
 * @param {Object} state
 * @param {string} schemaId The full schema ID like ithemes-security-user-group
 * @return {Object|null} The schema.
 */
export function getSchema( state, schemaId ) {
	const index = state.index;

	if ( ! index ) {
		return null;
	}

	return find( index.routes, ( route ) => route?.schema?.title === schemaId )?.schema;
}

export function getRoles( state ) {
	return state.index?.roles || null;
}

export function getRequirementsInfo( state ) {
	return state.index?.requirements_info || null;
}

export function getActorTypes( state ) {
	return state.actors.types;
}

export function getActors( state, type ) {
	return state.actors.byType[ type ];
}

export function getSiteInfo( state ) {
	return state.siteInfo;
}

export const getFeatureFlags = createRegistrySelector(
	( select ) => ( state ) => {
		const setting = select( MODULES_STORE_NAME ).getSetting(
			'feature-flags',
			'enabled'
		);

		return setting || state.featureFlags;
	}
);

export function getBatchMaxItems( state ) {
	return state.batchMaxItems;
}

export function getServerType( state ) {
	return state.index?.server_type || null;
}

export function getInstallType( state ) {
	return state.index?.install_type || null;
}

export function hasPatchstack( state ) {
	return state.index?.has_patchstack || null;
}

export function isLiquidWebCustomer( state ) {
	return state.index?.is_lw_customer || null;
}
