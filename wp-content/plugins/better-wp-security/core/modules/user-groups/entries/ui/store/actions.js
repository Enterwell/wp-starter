/**
 * External dependencies
 */
import {
	map,
	isString,
	isEqual,
	pickBy,
} from 'lodash';
import { v4 as uuidv4 } from 'uuid';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { controls } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { castWPError } from '@ithemes/security-utils';
import { store } from '@ithemes/security.user-groups.api';
import { STORE_NAME } from './constant';
import { createNotice } from './controls';

export function* editGroup( id, edit ) {
	const isLocal = yield controls.select(
		STORE_NAME,
		'isLocalGroup',
		id
	);
	const current =
		! isLocal &&
		( yield controls.select(
			store,
			'getGroup',
			id
		) );
	const allEdits = yield controls.select(
		STORE_NAME,
		'getEditedGroup',
		id
	);

	const merged = {
		...( allEdits || {} ),
		...edit,
	};

	const diff = pickBy(
		merged,
		( editedField, field ) => ! isEqual( current?.[ field ], editedField )
	);
	yield { type: EDIT_GROUP, id, edit: diff };
}

export function* saveGroup( id ) {
	const group = yield controls.select(
		STORE_NAME,
		'getEditedGroup',
		id
	);

	if ( ! group ) {
		return;
	}

	let updated;

	yield { type: START_SAVE_GROUP, id };

	if (
		yield controls.select(
			STORE_NAME,
			'isLocalGroup',
			id
		)
	) {
		updated = yield controls.dispatch(
			store,
			'createGroup',
			{ ...group, id }
		);
	} else {
		updated = yield controls.dispatch(
			store,
			'updateGroup',
			id,
			group
		);
	}

	if ( updated instanceof Error ) {
		yield { type: FAILED_SAVE_GROUP, id, error: updated };
	} else {
		yield { type: FINISH_SAVE_GROUP, id, updated };
		yield createNotice( 'success', __( 'Saved group.', 'better-wp-security' ), {
			type: 'snackbar',
		} );
	}

	return updated;
}

/**
 * Saves a batch of user groups.
 *
 * @param {boolean|string|Array<string>} groups The groups to save. By default, all dirty groups will be saved.
 * @return {Error|{responses: Array<Object>, byId: Object<Object>}} An error, or an object with the list of responses, and responses by id.
 */
export function* saveGroups( groups = true ) {
	const localGroups = yield controls.select(
		STORE_NAME,
		'getLocalGroupIds'
	);
	const markedForDeletion = yield controls.select(
		STORE_NAME,
		'getGroupsMarkedForDeletion'
	);

	if ( groups === true ) {
		groups = [
			...new Set( [
				...( yield controls.select(
					STORE_NAME,
					'getDirtyGroups'
				) ),
				...localGroups,
				...markedForDeletion,
			] ),
		];
	} else if ( isString( groups ) ) {
		groups = [ groups ];
	}

	if ( ! groups.length ) {
		return [];
	}

	const update = [];
	const create = [];
	const toDelete = [];

	for ( const group of groups ) {
		const edits = yield controls.select(
			STORE_NAME,
			'getEditedGroup',
			group
		);

		if ( markedForDeletion.includes( group ) ) {
			toDelete.push( group );
		} else if ( localGroups.includes( group ) ) {
			create.push( { ...edits, id: group } );
		} else {
			update.push( {
				...edits,
				id: group,
			} );
		}
	}

	const saved = yield controls.dispatch(
		store,
		'saveGroups',
		{ create, update, delete: toDelete }
	);

	if ( saved instanceof Error ) {
		yield createNotice(
			'error',
			sprintf(
				/* translators: 1. Error message. */
				__( 'Could not save user groups: %s', 'better-wp-security' ),
				saved.message
			)
		);
		return saved;
	}

	for ( const [ id, response ] of Object.entries( saved.byId ) ) {
		if ( response.status >= 400 ) {
			yield { type: FAILED_SAVE_GROUP, id, error: response.body };
		} else {
			yield { type: FINISH_SAVE_GROUP, id, updated: response.body };
		}
	}

	yield createNotice( 'success', __( 'Saved user groups.', 'better-wp-security' ), {
		type: 'snackbar',
	} );

	return saved;
}

export function resetEdits( id ) {
	return {
		type: RESET_EDITS,
		id,
	};
}

export function resetAllEdits() {
	return {
		type: RESET_ALL_EDITS,
	};
}

export function markGroupForDeletion( id ) {
	return {
		type: MARK_GROUP_FOR_DELETION,
		id,
	};
}

export function* deleteGroup( id ) {
	const isLocal = yield controls.select(
		STORE_NAME,
		'isLocalGroup',
		id
	);

	if ( isLocal ) {
		yield { type: DELETE_LOCAL_GROUP, id };
	} else {
		const deleted = yield controls.dispatch(
			store,
			'deleteGroup',
			id
		);

		if ( deleted instanceof Error ) {
			yield { type: SET_GROUP_ERROR, id, error: deleted };

			return deleted;
		}
	}

	yield resetEdits( id );
	yield { type: SET_GROUP_ERROR, id, error: null };
}

export function deleteLocalGroups() {
	return {
		type: DELETE_LOCAL_GROUPS,
	};
}

export function createLocalGroup( id ) {
	return {
		type: CREATE_LOCAL_GROUP,
		id: id || uuidv4(),
	};
}

export function* editGroupSetting( id, module, setting, value ) {
	const isLocal = yield controls.select(
		STORE_NAME,
		'isLocalGroup',
		id
	);
	const current =
		! isLocal &&
		( yield controls.select(
			store,
			'getGroupSetting',
			id,
			module,
			setting
		) );

	if ( isEqual( current, value ) ) {
		yield { type: RESET_GROUP_SETTING, id, module, setting };
	} else {
		yield {
			type: EDIT_GROUP_SETTING,
			id,
			module,
			setting,
			value,
		};
	}
}

export function* saveGroupSettings( id ) {
	const settings = yield controls.select(
		STORE_NAME,
		'getGroupSettingsEdits',
		id
	);

	if ( ! settings ) {
		return;
	}

	yield { type: START_SAVE_GROUP_SETTINGS, id };

	const updated = yield controls.dispatch(
		store,
		'updateGroupSettings',
		id,
		settings
	);

	if ( updated instanceof Error ) {
		yield { type: FAILED_SAVE_GROUP_SETTINGS, id, error: updated };
	} else {
		yield { type: FINISH_SAVE_GROUP_SETTINGS, id, updated };
		yield createNotice(
			'success',
			__( 'Updated group settings.', 'better-wp-security' ),
			{ type: 'snackbar' }
		);
	}

	return updated;
}

/**
 * Saves a batch of user groups.
 *
 * @param {boolean|string|Array<string>} groups The groups to save. By default, all dirty groups will be saved.
 * @return {Error|{responses: Array<Object>, byId: Object<Object>}} An error, or an object with the list of responses, and responses by id.
 */
export function* saveGroupSettingsAsBatch( groups = true ) {
	if ( groups === true ) {
		groups = yield controls.select(
			STORE_NAME,
			'getDirtyGroupSettings'
		);
	} else if ( isString( groups ) ) {
		groups = [ groups ];
	}

	if ( ! groups.length ) {
		return { responses: [], byId: {} };
	}

	const save = {};

	for ( const group of groups ) {
		save[ group ] = yield controls.select(
			STORE_NAME,
			'getGroupSettingsEdits',
			group
		);
	}

	const saved = yield controls.dispatch(
		store,
		'saveGroupSettingsAsBatch',
		save
	);

	if ( saved instanceof Error ) {
		yield createNotice(
			'error',
			sprintf(
				/* translators: 1. Error message. */
				__( 'Could not save user groups: %s', 'better-wp-security' ),
				saved.message
			)
		);
		return saved;
	}

	for ( const [ id, response ] of Object.entries( saved.byId ) ) {
		if ( response.status >= 400 ) {
			yield {
				type: FAILED_SAVE_GROUP_SETTINGS,
				id,
				error: response.body,
			};
		} else {
			yield {
				type: FINISH_SAVE_GROUP_SETTINGS,
				id,
				updated: response.body,
			};
		}
	}

	yield createNotice( 'success', __( 'Saved user groups.', 'better-wp-security' ), {
		type: 'snackbar',
	} );

	return saved;
}

export function bulkEditGroupSetting( module, setting, value ) {
	return {
		type: BULK_EDIT_GROUP_SETTING,
		module,
		setting,
		value,
	};
}

export function resetBulkGroupSettingEdit( module, setting ) {
	return {
		type: RESET_BULK_GROUP_SETTING_EDIT,
		module,
		setting,
	};
}

export function resetBulkGroupSettingEdits() {
	return {
		type: RESET_BULK_GROUP_SETTING_EDITS,
	};
}

export function* saveBulkEdits( groupIds ) {
	const edits = yield controls.select(
		STORE_NAME,
		'getBulkSettingEdits'
	);
	const response = yield controls.dispatch(
		store,
		'patchBulkGroupSettings',
		groupIds,
		edits
	);

	if ( response instanceof Error ) {
		yield createNotice( 'error', response.message );

		return response;
	}

	if (
		map( response, 'status' ).every( ( status ) => status === 200 )
	) {
		yield createNotice(
			'success',
			__( 'Updated group settings.', 'better-wp-security' ),
			{ type: 'snackbar' }
		);
		yield resetBulkGroupSettingEdits();

		return response;
	}

	const errors = response
		.filter( ( { status } ) => status !== 200 )
		.flatMap( ( { error } ) =>
			castWPError( error ).getAllErrorMessages()
		);

	yield { type: SET_BULK_ERRORS, errors };

	return response;
}

export function* saveGroupAndSettings( id ) {
	if (
		yield controls.select(
			STORE_NAME,
			'hasEdits',
			id
		)
	) {
		const saved = yield* saveGroup( id );

		if ( saved instanceof Error ) {
			return saved;
		}
	}

	if (
		yield controls.select(
			STORE_NAME,
			'settingHasEdits',
			id
		)
	) {
		const saved = yield* saveGroupSettings( id );

		if ( saved instanceof Error ) {
			return saved;
		}
	}

	return null;
}

export function createdDefaultGroups() {
	return {
		type: CREATED_DEFAULT_GROUPS,
	};
}

export const SET_GROUP_ERROR = 'SET_GROUP_ERROR';
export const SET_BULK_ERRORS = 'SET_BULK_ERRORS';

export const EDIT_GROUP = 'EDIT_GROUP';
export const RESET_EDITS = 'RESET_EDITS';
export const RESET_ALL_EDITS = 'RESET_ALL_EDITS';

export const CREATE_LOCAL_GROUP = 'CREATE_LOCAL_GROUP';
export const DELETE_LOCAL_GROUP = 'DELETE_LOCAL_GROUP';
export const DELETE_LOCAL_GROUPS = 'DELETE_LOCAL_GROUPS';

export const MARK_GROUP_FOR_DELETION = 'MARK_GROUP_FOR_DELETION';

export const START_SAVE_GROUP = 'START_SAVE_GROUP';
export const FINISH_SAVE_GROUP = 'FINISH_SAVE_GROUP';
export const FAILED_SAVE_GROUP = 'FAILED_SAVE_GROUP';

export const EDIT_GROUP_SETTING = 'EDIT_GROUP_SETTING';
export const RESET_GROUP_SETTING = 'RESET_GROUP_SETTING';

export const START_SAVE_GROUP_SETTINGS = 'START_SAVE_GROUP_SETTINGS';
export const FINISH_SAVE_GROUP_SETTINGS = 'FINISH_SAVE_GROUP_SETTINGS';
export const FAILED_SAVE_GROUP_SETTINGS = 'FAILED_SAVE_GROUP_SETTINGS';

export const BULK_EDIT_GROUP_SETTING = 'BULK_EDIT_GROUP_SETTING';
export const RESET_BULK_GROUP_SETTING_EDIT = 'RESET_BULK_GROUP_SETTING_EDIT';
export const RESET_BULK_GROUP_SETTING_EDITS = 'RESET_BULK_GROUP_SETTING_EDITS';

export const CREATED_DEFAULT_GROUPS = 'CREATED_DEFAULT_GROUPS';
