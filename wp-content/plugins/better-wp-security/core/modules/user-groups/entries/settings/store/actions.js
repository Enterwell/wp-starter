/**
 * External dependencies
 */
import { get, map } from 'lodash';

/**
 * WordPress dependencies
 */
import { __, _n, sprintf } from '@wordpress/i18n';
import { dispatch as dataDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { castWPError } from '@ithemes/security-utils';
import { select, dispatch, createNotice } from './controls';

export function selectGroup( ids ) {
	return {
		type: SELECT_GROUP,
		ids,
	};
}

export function editGroup( id, edit ) {
	return {
		type: EDIT_GROUP,
		id,
		edit,
	};
}

export function* saveGroup( id ) {
	const group = yield select( 'ithemes-security/user-groups-editor', 'getEditedGroup', id );

	if ( ! group ) {
		return;
	}

	yield { type: START_SAVE_GROUP, id };

	const updated = yield dispatch( 'ithemes-security/user-groups', 'updateGroup', id, group );

	if ( updated instanceof Error ) {
		yield createNotice( 'error', updated.message );
		yield { type: FAILED_SAVE_GROUP, id, error: updated };
	} else {
		yield { type: FINISH_SAVE_GROUP, id, updated };
		yield createNotice( 'success', __( 'Updated group.', 'better-wp-security' ), { type: 'snackbar' } );
	}

	return updated;
}

export function resetEdits( id ) {
	return {
		type: RESET_EDITS,
		id,
	};
}

export function* createGroup( args = {} ) {
	const group = yield select( 'ithemes-security/user-groups-editor', 'getEditedGroup', 'new' );

	if ( ! group ) {
		return;
	}

	yield { type: START_CREATE_GROUP };
	const created = yield dispatch( 'ithemes-security/user-groups', 'createGroup', {
		...group,
		...args,
	} );

	if ( created instanceof Error ) {
		if ( created.code === 'rest_duplicate_user_group' ) {
			yield createNotice( 'error', created.message, {
				actions: [
					{
						label: __( 'View Duplicate', 'better-wp-security' ),
						isLink: true,
						onClick() {
							const duplicateLink = get( created, [ '_links', 'duplicate', 0, 'href' ] );
							const duplicateId = duplicateLink.split( '/' ).pop();

							dataDispatch( 'ithemes-security/user-groups-editor' ).selectGroup( [ duplicateId ] );
						},
					},
					{
						label: __( 'Create Anyway', 'better-wp-security' ),
						onClick() {
							dataDispatch( 'ithemes-security/user-groups-editor' ).createGroup( {
								ignore_duplicate: true,
							} );
						},
					},
				],
			} );
		} else {
			yield createNotice( 'error', created.message );
		}

		yield { type: FAILED_CREATE_GROUP, error: created };
	} else {
		yield resetEdits( 'new' );
		yield { type: FINISH_CREATE_GROUP, created };
		yield dispatch( 'ithemes-security/user-groups-editor', 'selectGroup', created.id );
		yield createNotice( 'success', __( 'Created group.', 'better-wp-security' ), { type: 'snackbar' } );
	}

	return created;
}

export function editGroupSetting( id, module, setting, value ) {
	return {
		type: EDIT_GROUP_SETTING,
		id,
		module,
		setting,
		value,
	};
}

export function* saveGroupSettings( id ) {
	const settings = yield select( 'ithemes-security/user-groups-editor', 'getEditedGroupSettings', id );

	if ( ! settings ) {
		return;
	}

	yield { type: START_SAVE_GROUP_SETTINGS, id };

	const updated = yield dispatch( 'ithemes-security/user-groups', 'updateGroupSettings', id, settings );

	if ( updated instanceof Error ) {
		yield createNotice( 'error', updated.message );
		yield { type: FAILED_SAVE_GROUP_SETTINGS, id, error: updated };
	} else {
		yield { type: FINISH_SAVE_GROUP_SETTINGS, id, updated };
		yield createNotice( 'success', __( 'Updated group settings.', 'better-wp-security' ), { type: 'snackbar' } );
	}

	return updated;
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
	const edits = yield select( 'ithemes-security/user-groups-editor', 'getBulkSettingEdits' );
	const response = yield dispatch(
		'ithemes-security/user-groups',
		'patchBulkGroupSettings',
		groupIds,
		edits
	);

	if ( response instanceof Error ) {
		yield createNotice( 'error', response.message );
	} else if ( map( response, 'status' ).every( ( status ) => status === 200 ) ) {
		yield createNotice( 'success', __( 'Updated group settings.', 'better-wp-security' ), { type: 'snackbar' } );
	} else {
		const errors = response.filter( ( { status } ) => status !== 200 ).map( ( { error } ) => castWPError( error ) );
		const combinedMessage = errors.map( ( error ) => error.getAllErrorMessages().join( ' ' ) ).join( ' ' );

		if ( errors.length === response.length ) {
			yield createNotice( 'error', combinedMessage );
		} else {
			yield createNotice(
				'warning',
				sprintf(
					_n( '%1$d group was not updated: %2$s', '%1$d groups were not updated: %2$s', errors.length, 'better-wp-security' ),
					errors.length,
					combinedMessage
				)
			);
		}
	}

	yield resetBulkGroupSettingEdits();

	return response;
}

export const SELECT_GROUP = 'SELECT_GROUP';

export const EDIT_GROUP = 'EDIT_GROUP';
export const RESET_EDITS = 'RESET_EDITS';

export const START_SAVE_GROUP = 'START_SAVE_GROUP';
export const FINISH_SAVE_GROUP = 'FINISH_SAVE_GROUP';
export const FAILED_SAVE_GROUP = 'FAILED_SAVE_GROUP';

export const START_CREATE_GROUP = 'START_CREATE_GROUP';
export const FINISH_CREATE_GROUP = 'FINISH_CREATE_GROUP';
export const FAILED_CREATE_GROUP = 'FAILED_CREATE_GROUP';

export const EDIT_GROUP_SETTING = 'EDIT_GROUP_SETTING';

export const START_SAVE_GROUP_SETTINGS = 'START_SAVE_GROUP_SETTINGS';
export const FINISH_SAVE_GROUP_SETTINGS = 'FINISH_SAVE_GROUP_SETTINGS';
export const FAILED_SAVE_GROUP_SETTINGS = 'FAILED_SAVE_GROUP_SETTINGS';

export const BULK_EDIT_GROUP_SETTING = 'BULK_EDIT_GROUP_SETTING';
export const RESET_BULK_GROUP_SETTING_EDIT = 'RESET_BULK_GROUP_SETTING_EDIT';
export const RESET_BULK_GROUP_SETTING_EDITS = 'RESET_BULK_GROUP_SETTING_EDITS';
