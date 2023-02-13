/**
 * External dependencies
 */
import { get, isEmpty } from 'lodash';

/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data';

/**
 * Get the group being edited.
 * @param {Object} state
 * @return {Array<string>}
 */
export function getSelectedGroup( state ) {
	return state.selectedGroup;
}

/**
 * Is a new group being created.
 * @param {Object} state
 * @return {boolean}
 */
export function isCreating( state ) {
	return state.creating;
}

/**
 * Get the full edited group object.
 * @param {Object} state
 * @param {string} id
 * @return {Object|undefined}
 */
export function getEditedGroup( state, id ) {
	return state.edits[ id ];
}

/**
 * Get the edited attribute for a group.
 * @param {Object} state
 * @param {string} id
 * @param {string} attribute
 * @return {*}
 */
export function getEditedGroupAttribute( state, id, attribute ) {
	const edited = get( state, [ 'edits', id, attribute ] );

	if ( typeof edited !== 'undefined' ) {
		return edited;
	}

	if ( id === 'new' ) {
		return undefined;
	}

	return select( 'ithemes-security/user-groups' ).getGroupAttribute( id, attribute );
}

/**
 * Checks if there are changes to be saved for a group.
 * @param {Object} state
 * @param {string} id
 * @return {boolean}
 */
export function hasEdits( state, id ) {
	return !! state.edits[ id ];
}

/**
 * Are there any unsaved changes for a group's settings.
 * @param {Object} state
 * @param {string} id
 * @return {boolean}
 */
export function settingHasEdits( state, id ) {
	return !! state.settingEdits[ id ];
}

/**
 * Get the full edited group settings.
 *
 * @param {Object} state
 * @param {string} id
 * @return {Object}
 */
export function getEditedGroupSettings( state, id ) {
	return state.settingEdits[ id ];
}

/**
 * Get a group's edited value for a setting.
 *
 * @param {Object} state
 * @param {string} id
 * @param {string} module
 * @param {string} setting
 * @return {boolean}
 */
export function getEditedGroupSetting( state, id, module, setting ) {
	const value = get( state, [ 'settingEdits', id, module, setting ] );

	if ( value !== undefined ) {
		return value;
	}

	return select( 'ithemes-security/user-groups' ).getGroupSetting( id, module, setting );
}

/**
 * Check if there have been bulk setting edits.
 * @param {Object} state
 * @return {boolean}
 */
export function hasBulkSettingEdits( state ) {
	return ! isEmpty( state.bulkSettingEdits );
}

/**
 * Get all the bulk setting edits.
 * @param {Object} state
 * @return {{}}
 */
export function getBulkSettingEdits( state ) {
	return state.bulkSettingEdits;
}

/**
 * Get the bulk edited setting value.
 *
 * @param {Object} state
 * @param {string} module
 * @param {string} setting
 * @return {boolean|undefined}
 */
export function getBulkSettingEdit( state, module, setting ) {
	return get( state, [ 'bulkSettingEdits', module, setting ] );
}

/**
 * Get the value for a bulk edited setting.
 *
 * @param {Object} state
 * @param {Array<Object>} groupIds
 * @param {string} module
 * @param {string} setting
 * @return {null|boolean}
 */
export function getBulkSettingValue( state, groupIds, module, setting ) {
	const edit = getBulkSettingEdit( state, module, setting );

	if ( edit !== undefined ) {
		return edit;
	}

	const getValue = ( groupId ) => select( 'ithemes-security/user-groups' ).getGroupSetting( groupId, module, setting );

	const firstVal = getValue( groupIds[ 0 ] );
	const allSame = groupIds.every( ( groupId ) => getValue( groupId ) === firstVal );

	if ( allSame ) {
		return firstVal;
	}

	return null;
}

/**
 * Are bulk edits being saved.
 *
 * @param {Object} state The state object.
 * @param {Array<string>} groupIds The list of group ids.
 * @return {boolean}
 */
export function isSavingBulkEdits( state, groupIds ) {
	const edits = getBulkSettingEdits( state );

	return select( 'ithemes-security/user-groups' ).isBulkPatchingSettings( groupIds, edits );
}

/**
 * Get the list of available groups.
 *
 * @return {Array<Object>}
 */
export function getAvailableGroups() {
	return select( 'ithemes-security/user-groups' ).getGroups( 'available' );
}
