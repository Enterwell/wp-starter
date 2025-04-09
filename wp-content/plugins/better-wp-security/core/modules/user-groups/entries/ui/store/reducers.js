/**
 * External dependencies
 */
import { get, omit, isEmpty } from 'lodash';

/**
 * Internal dependencies
 */
import {
	EDIT_GROUP,
	START_SAVE_GROUP,
	FAILED_SAVE_GROUP,
	FINISH_SAVE_GROUP,
	EDIT_GROUP_SETTING,
	FINISH_SAVE_GROUP_SETTINGS,
	RESET_EDITS,
	BULK_EDIT_GROUP_SETTING,
	RESET_BULK_GROUP_SETTING_EDIT,
	RESET_BULK_GROUP_SETTING_EDITS,
	CREATE_LOCAL_GROUP,
	DELETE_LOCAL_GROUP,
	RESET_ALL_EDITS,
	DELETE_LOCAL_GROUPS,
	FAILED_SAVE_GROUP_SETTINGS,
	SET_GROUP_ERROR,
	SET_BULK_ERRORS,
	RESET_GROUP_SETTING,
	MARK_GROUP_FOR_DELETION,
	CREATED_DEFAULT_GROUPS,
} from './actions';

const DEFAULT_STATE = {
	edits: {},
	settingEdits: {},
	bulkSettingEdits: {},
	localGroupIds: [],
	saving: [],
	errors: {},
	bulkErrors: [],
	markedForDelete: [],
	createdDefaultGroups: false,
};

export default function userGroupsEditor( state = DEFAULT_STATE, action ) {
	switch ( action.type ) {
		case EDIT_GROUP:
			return {
				...state,
				edits: {
					...state.edits,
					[ action.id ]: action.edit,
				},
			};
		case CREATE_LOCAL_GROUP:
			return {
				...state,
				localGroupIds: [ ...state.localGroupIds, action.id ],
			};
		case DELETE_LOCAL_GROUP:
			return {
				...state,
				localGroupIds: state.localGroupIds.filter(
					( groupId ) => groupId !== action.id
				),
			};
		case DELETE_LOCAL_GROUPS:
			return {
				...state,
				localGroupIds: [],
			};
		case START_SAVE_GROUP:
			return {
				...state,
				saving: [ ...state.saving, action.id ],
			};
		case FAILED_SAVE_GROUP:
			return {
				...state,
				saving: state.saving.filter( ( id ) => id !== action.id ),
				errors: {
					...state.errors,
					[ action.id ]: action.error,
				},
			};
		case FINISH_SAVE_GROUP:
			return {
				...state,
				edits: omit( state.edits, [ action.id ] ),
				localGroupIds: state.localGroupIds.filter(
					( groupId ) => groupId !== action.id
				),
				saving: state.saving.filter( ( id ) => id !== action.id ),
				errors: omit( state.errors, [ action.id ] ),
			};
		case EDIT_GROUP_SETTING:
			return {
				...state,
				settingEdits: {
					...state.settingEdits,
					[ action.id ]: {
						...get( state, [ 'settingEdits', action.id ], {} ),
						[ action.module ]: {
							...get(
								state,
								[ 'settingEdits', action.id, action.module ],
								{}
							),
							[ action.setting ]: action.value,
						},
					},
				},
			};
		case RESET_GROUP_SETTING:
			const next = {
				...state,
				settingEdits: {
					...state.settingEdits,
					[ action.id ]: {
						...get( state, [ 'settingEdits', action.id ], {} ),
						[ action.module ]: omit(
							get(
								state,
								[ 'settingEdits', action.id, action.module ],
								{}
							),
							[ action.setting ]
						),
					},
				},
			};

			if ( isEmpty( next.settingEdits[ action.id ][ action.module ] ) ) {
				delete next.settingEdits[ action.id ][ action.module ];
			}

			if ( isEmpty( next.settingEdits[ action.id ] ) ) {
				delete next.settingEdits[ action.id ];
			}

			return next;
		case FINISH_SAVE_GROUP_SETTINGS:
			return {
				...state,
				settingEdits: omit( state.settingEdits, [ action.id ] ),
				errors: omit( state.errors, [ action.id ] ),
			};
		case FAILED_SAVE_GROUP_SETTINGS:
			return {
				...state,
				errors: {
					...state.errors,
					[ action.id ]: action.error,
				},
			};
		case RESET_EDITS:
			return {
				...state,
				edits: omit( state.edits, [ action.id ] ),
				settingEdits: omit( state.settingEdits, [ action.id ] ),
				markedForDelete: state.markedForDelete.filter(
					( id ) => id !== action.id
				),
				localGroupIds: state.localGroupIds.filter(
					( id ) => id !== action.id
				),
			};
		case RESET_ALL_EDITS:
			return {
				...state,
				edits: {},
				settingEdits: {},
				bulkSettingEdits: {},
				markedForDelete: [],
				localGroupIds: [],
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
				bulkSettingEdits: omit(
					state.bulkSettingEdits,
					`${ action.module }.${ action.setting }`
				),
			};
		case RESET_BULK_GROUP_SETTING_EDITS:
			return {
				...state,
				bulkSettingEdits: {},
				bulkErrors: [],
			};
		case MARK_GROUP_FOR_DELETION:
			return {
				...state,
				markedForDelete: [ ...state.markedForDelete, action.id ],
			};
		case SET_GROUP_ERROR:
			return {
				...state,
				errors: action.error
					? {
						...state.errors,
						[ action.id ]: action.error,
					}
					: omit( state.errors, [ action.id ] ),
			};
		case SET_BULK_ERRORS:
			return {
				...state,
				bulkErrors: action.errors,
			};
		case CREATED_DEFAULT_GROUPS:
			return {
				...state,
				createdDefaultGroups: true,
			};
		default:
			return state;
	}
}
