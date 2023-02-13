/**
 * External dependencies
 */
import { get } from 'lodash';
import UriTemplate from 'uri-templates';

/**
 * WordPress dependencies
 */
import { addQueryArgs } from '@wordpress/url';
import { select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { getSchemaLink } from '@ithemes/security-utils';
import { apiFetch, dispatch } from './controls';

export const path = '/ithemes-security/v1/user-groups';

export function* query( queryId, queryParams ) {
	const items = yield apiFetch( {
		path: addQueryArgs( path, queryParams ),
	} );
	yield receiveQuery( queryId, items );

	for ( const item of items ) {
		yield* processItem( item );
	}

	return items;
}

export function* appendToQuery( queryId, item ) {
	yield {
		type: APPEND_TO_QUERY,
		queryId,
		item,
	};
	yield* processItem( item );
}

export function receiveQuery( queryId, items ) {
	return {
		type: RECEIVE_QUERY,
		queryId,
		items,
	};
}

export function* processItem( item ) {
	const users = get( item, [ '_embedded', 'ithemes-security:user-group-member' ], [] );
	const settings = get( item, [ '_embedded', 'ithemes-security:user-matchable-settings', 0 ] );

	for ( const user of users ) {
		yield dispatch( 'ithemes-security/core', 'receiveUser', user );
	}

	if ( settings ) {
		yield receiveGroupSettings( item.id, settings );
	}
}

export function receiveGroup( group ) {
	return {
		type: RECEIVE_GROUP,
		group,
	};
}

export function receiveMatchables( matchables ) {
	return {
		type: RECEIVE_MATCHABLES,
		matchables,
	};
}

export function startCreateGroup( group ) {
	return { type: START_CREATE_GROUP, group };
}

export function failedCreateGroup( group, error ) {
	return { type: FAILED_CREATE_GROUP, group, error };
}

export function finishCreateGroup( group, response ) {
	return { type: FINISH_CREATE_GROUP, group, response };
}

export function startUpdateGroup( id, group ) {
	return { type: START_UPDATE_GROUP, id, group };
}

export function failedUpdateGroup( id, error ) {
	return { type: FAILED_UPDATE_GROUP, id, error };
}

export function finishUpdateGroup( id, response ) {
	return { type: FINISH_UPDATE_GROUP, id, response };
}

export function startDeleteGroup( id ) {
	return { type: START_DELETE_GROUP, id };
}

export function failedDeleteGroup( id, error ) {
	return { type: FAILED_DELETE_GROUP, id, error };
}

export function finishDeleteGroup( id ) {
	return { type: FINISH_DELETE_GROUP, id };
}

export function receiveGroupSettings( id, settings ) {
	return {
		type: RECEIVE_GROUP_SETTINGS,
		id,
		settings,
	};
}

export function startUpdateGroupSettings( id, settings ) {
	return { type: START_UPDATE_GROUP_SETTINGS, id, settings };
}

export function failedUpdateGroupSettings( id, error ) {
	return { type: FAILED_UPDATE_GROUP_SETTINGS, id, error };
}

export function finishUpdateGroupSettings( id, response ) {
	return { type: FINISH_UPDATE_GROUP_SETTINGS, id, response };
}

/**
 * Creates a new user group.
 *
 * @param {Object} group Group data.
 * @return {IterableIterator<*>} Iterator
 */
export function* createGroup( group ) {
	yield startCreateGroup( group );

	let response;

	try {
		response = yield apiFetch( {
			path: addQueryArgs( path, { _embed: 1 } ),
			method: 'POST',
			data: group,
		} );
	} catch ( e ) {
		yield failedCreateGroup( group, e );
		return e;
	}

	yield finishCreateGroup( group, response );
	yield receiveGroup( response );
	yield* processItem( response );

	return response;
}

/**
 * Updates a user group.
 *
 * @param {string} id Group id id to update.
 * @param {Object} group Group data.
 * @return {IterableIterator<*>} Iterator
 */
export function* updateGroup( id, group ) {
	yield startUpdateGroup( id, group );

	let response;

	try {
		response = yield apiFetch( {
			path: path + '/' + id,
			method: 'PUT',
			data: group,
		} );
	} catch ( e ) {
		yield failedUpdateGroup( id, e );
		return e;
	}

	yield finishUpdateGroup( id, response );
	yield receiveGroup( response );

	return response;
}

/**
 * Deletes a user group.
 *
 * @param {string} id Group id to delete.
 * @return {IterableIterator<*>} Iterator
 */
export function* deleteGroup( id ) {
	yield startDeleteGroup( id );

	try {
		yield apiFetch( {
			path: `${ path }/${ id }`,
			method: 'DELETE',
		} );
	} catch ( e ) {
		yield failedDeleteGroup( id, e );
		return e;
	}

	yield finishDeleteGroup( id );

	return null;
}

/**
 * Updates a user group.
 *
 * @param {Object} id Id of group.
 * @param {Object} settings New settings.
 * @return {IterableIterator<*>} Iterator
 */
export function* updateGroupSettings( id, settings ) {
	yield startUpdateGroupSettings( id, settings );

	let response;

	try {
		response = yield apiFetch( {
			path: `ithemes-security/v1/user-matchable-settings/${ id }`,
			method: 'PUT',
			data: settings,
		} );
	} catch ( e ) {
		yield failedUpdateGroupSettings( id, e );
		return e;
	}

	yield finishUpdateGroupSettings( id, response );
	yield receiveGroupSettings( id, response );

	return response;
}

export function* fetchGroupsSettings( groupIds = [] ) {
	yield startFetchGroupsSettings( groupIds );

	let response;

	try {
		let fetchPath = 'ithemes-security/v1/user-matchable-settings';

		if ( groupIds.length > 0 ) {
			fetchPath = addQueryArgs( fetchPath, { include: groupIds } );
		}

		response = yield apiFetch( {
			path: fetchPath,
		} );
	} catch ( e ) {
		yield failedFetchGroupsSettings( groupIds, e );

		return e;
	}

	yield finishFetchGroupsSettings( groupIds, response );

	for ( const groupId in response ) {
		if ( ! response.hasOwnProperty( groupId ) ) {
			continue;
		}

		yield receiveGroupSettings( groupId, response[ groupId ] );
	}

	return response;
}

export function startFetchGroupsSettings( groupIds ) {
	return {
		type: START_FETCH_GROUPS_SETTINGS,
		groupIds,
	};
}

export function finishFetchGroupsSettings( groupIds, response ) {
	return {
		type: FINISH_FETCH_GROUPS_SETTINGS,
		groupIds,
		response,
	};
}

export function failedFetchGroupsSettings( groupIds, error ) {
	return {
		type: FAILED_FETCH_GROUPS_SETTINGS,
		groupIds,
		error,
	};
}

export function* patchBulkGroupSettings( groupIds, patch ) {
	yield startPatchBulkGroupSettings( groupIds, patch );

	let response;

	try {
		response = yield apiFetch( {
			path: addQueryArgs( `ithemes-security/v1/user-matchable-settings`, { include: groupIds } ),
			method: 'PATCH',
			data: patch,
		} );
	} catch ( e ) {
		yield failedPatchBulkGroupSettings( groupIds, patch, e );

		return e;
	}

	yield finishPatchBulkGroupSettings( groupIds, patch, response );

	const schema = select( 'ithemes-security/core' ).getSchema( 'ithemes-security-user-group-settings' );
	const selfLink = getSchemaLink( schema,	'self' );

	if ( ! selfLink ) {
		return response;
	}

	const template = new UriTemplate( selfLink.href );

	for ( const result of response ) {
		if ( result.status !== 200 ) {
			continue;
		}

		const props = template.fromUri( result.href );

		if ( ! props.id ) {
			continue;
		}

		yield receiveGroupSettings( props.id, result.response );
	}

	return response;
}

export function startPatchBulkGroupSettings( groupIds, patch ) {
	return {
		type: START_PATCH_BULK_GROUP_SETTINGS,
		groupIds,
		patch,
	};
}

export function finishPatchBulkGroupSettings( groupIds, patch, response ) {
	return {
		type: FINISH_PATCH_BULK_GROUP_SETTINGS,
		groupIds,
		patch,
		response,
	};
}

export function failedPatchBulkGroupSettings( groupIds, patch, error ) {
	return {
		type: FAILED_PATCH_BULK_GROUP_SETTINGS,
		groupIds,
		patch,
		error,
	};
}

export const RECEIVE_QUERY = 'RECEIVE_QUERY';
export const APPEND_TO_QUERY = 'APPEND_TO_QUERY';

export const RECEIVE_MATCHABLES = 'RECEIVE_MATCHABLES';

export const START_CREATE_GROUP = 'START_CREATE_GROUP';
export const FINISH_CREATE_GROUP = 'FINISH_CREATE_GROUP';
export const FAILED_CREATE_GROUP = 'FAILED_CREATE_GROUP';

export const RECEIVE_GROUP = 'RECEIVE_GROUP';

export const START_UPDATE_GROUP = 'START_UPDATE_GROUP';
export const FINISH_UPDATE_GROUP = 'FINISH_UPDATE_GROUP';
export const FAILED_UPDATE_GROUP = 'FAILED_UPDATE_GROUP';

export const START_DELETE_GROUP = 'START_DELETE_GROUP';
export const FINISH_DELETE_GROUP = 'FINISH_DELETE_GROUP';
export const FAILED_DELETE_GROUP = 'FAILED_DELETE_GROUP';

export const RECEIVE_GROUP_SETTINGS = 'RECEIVE_GROUP_SETTINGS';

export const START_UPDATE_GROUP_SETTINGS = 'START_UPDATE_GROUP_SETTINGS';
export const FINISH_UPDATE_GROUP_SETTINGS = 'FINISH_UPDATE_GROUP_SETTINGS';
export const FAILED_UPDATE_GROUP_SETTINGS = 'FAILED_UPDATE_GROUP_SETTINGS';

export const START_FETCH_GROUPS_SETTINGS = 'START_FETCH_GROUPS_SETTINGS';
export const FINISH_FETCH_GROUPS_SETTINGS = 'FINISH_FETCH_GROUPS_SETTINGS';
export const FAILED_FETCH_GROUPS_SETTINGS = 'FAILED_FETCH_GROUPS_SETTINGS';

export const START_PATCH_BULK_GROUP_SETTINGS = 'START_PATCH_BULK_GROUP_SETTINGS';
export const FINISH_PATCH_BULK_GROUP_SETTINGS = 'FINISH_PATCH_BULK_GROUP_SETTINGS';
export const FAILED_PATCH_BULK_GROUP_SETTINGS = 'FAILED_PATCH_BULK_GROUP_SETTINGS';
