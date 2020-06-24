/**
 * External dependencies
 */
import { get, omit } from 'lodash';

/**
 * Internal dependencies
 */
import {
	EDIT_GROUP,
	FINISH_SAVE_GROUP,
	EDIT_GROUP_SETTING,
	FINISH_SAVE_GROUP_SETTINGS,
	START_CREATE_GROUP,
	FINISH_CREATE_GROUP,
	FAILED_CREATE_GROUP,
	RESET_EDITS,
	SELECT_GROUP,
	BULK_EDIT_GROUP_SETTING, RESET_BULK_GROUP_SETTING_EDIT, RESET_BULK_GROUP_SETTING_EDITS,
} from './actions';

const DEFAULT_STATE = {
	edits: {},
	settingEdits: {},
	bulkSettingEdits: {},
	creating: false,
	selectedGroup: [],
};

export default function userGroupsEditor( state = DEFAULT_STATE, action ) {
	switch ( action.type ) {
		case SELECT_GROUP:
			return {
				...state,
				selectedGroup: action.ids,
			};
		case EDIT_GROUP:
			return {
				...state,
				edits: {
					...state.edits,
					[ action.id ]: {
						...( state.edits[ action.id ] || {} ),
						...action.edit,
					},
				},
			};
		case FINISH_SAVE_GROUP:
			return {
				...state,
				edits: omit( state.edits, [ action.id ] ),
			};
		case EDIT_GROUP_SETTING:
			return {
				...state,
				settingEdits: {
					...state.settingEdits,
					[ action.id ]: {
						...get( state, [ 'settingEdits', action.id ], {} ),
						[ action.module ]: {
							...get( state, [ 'settingEdits', action.id, action.module ], {} ),
							[ action.setting ]: action.value,
						},
					},
				},
			};
		case FINISH_SAVE_GROUP_SETTINGS:
			return {
				...state,
				settingEdits: omit( state.settingEdits, [ action.id ] ),
			};
		case START_CREATE_GROUP:
			return {
				...state,
				creating: true,
			};
		case FAILED_CREATE_GROUP:
			return {
				...state,
				creating: false,
			};
		case FINISH_CREATE_GROUP:
			return {
				...state,
				creating: false,
			};
		case RESET_EDITS:
			return {
				...state,
				edits: omit( state.edits, [ action.id ] ),
			};
		case BULK_EDIT_GROUP_SETTING:
			return {
				...state,
				bulkSettingEdits: {
					...state.bulkSettingEdits,
					[ action.module ]: {
						...( state.bulkSettingEdits[ action.module ] || {} ),
						[ action.setting ]: action.value,
					},
				},
			};
		case RESET_BULK_GROUP_SETTING_EDIT:
			return {
				...state,
				bulkSettingEdits: omit( state.bulkSettingEdits, `${ action.module }.${ action.setting }` ),
			};
		case RESET_BULK_GROUP_SETTING_EDITS:
			return {
				...state,
				bulkSettingEdits: {},
			};
		default:
			return state;
	}
}
