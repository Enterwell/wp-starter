/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data';

/**
 * Get a WP User by its ID.
 * @param {Object} state
 * @param {number} userId
 * @return {Object}
 */
export function getUser( state, userId ) {
	return state.users.byId[ userId ];
}

export function getIndex( state ) {
	return state.index;
}

/**
 * Get a schema from the root index.
 * @param {Object} state
 * @param {string} schemaId The full schema ID like ithemes-security-user-group
 * @return {Object|null}
 */
export function getSchema( state, schemaId ) {
	const index = select( 'ithemes-security/core' ).getIndex();

	if ( ! index ) {
		return null;
	}

	for ( const route in index.routes ) {
		if ( ! index.routes.hasOwnProperty( route ) ) {
			continue;
		}

		const schema = index.routes[ route ].schema;

		if ( schema && schema.title === schemaId ) {
			return schema;
		}
	}

	return null;
}

export function getRoles() {
	const index = select( 'ithemes-security/core' ).getIndex();

	if ( ! index ) {
		return null;
	}

	return index.roles;
}
