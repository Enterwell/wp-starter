/**
 * External dependencies
 */
import { get, omit, without } from 'lodash';

/**
 * Internal dependencies
 */
import {
	EDIT_MODULE,
	EDIT_SETTING,
	EDIT_SETTINGS,
	FAILED_SAVING_MODULES,
	FAILED_SAVING_SETTINGS,
	FINISH_SAVING_MODULES,
	FINISH_SAVING_SETTINGS,
	RECEIVE_MODULE,
	RECEIVE_MODULES,
	RECEIVE_SETTINGS,
	RESET_MODULE_EDITS,
	RESET_SETTING_EDIT,
	RESET_SETTING_EDITS,
	START_SAVING_MODULES,
	START_SAVING_SETTINGS,
} from './actions';

const DEFAULT_STATE = {
	modules: [],
	moduleEdits: {},
	savingModules: [],
	settings: {},
	settingEdits: {},
	savingSettings: [],
	errors: {},
};

export default function( state = DEFAULT_STATE, action ) {
	switch ( action.type ) {
		case RECEIVE_MODULES:
			return {
				...state,
				modules: [ ...action.modules ],
				settings: {
					...state.settings,
					...action.modules.reduce( ( acc, module ) => {
						const settings = get( module, [
							'_embedded',
							'ithemes-security:settings',
							0,
						] );

						if ( settings ) {
							return { ...acc, [ module.id ]: settings };
						}

						return acc;
					}, {} ),
				},
			};
		case RECEIVE_MODULE:
			return {
				...state,
				modules: state.modules.map( ( module ) =>
					module.id === action.module.id ? action.module : module
				),
				settings: {
					...state.settings,
					[ action.module.id ]: get(
						action.module,
						[ '_embedded', 'ithemes-security:settings', 0 ],
						state.settings[ action.module.id ]
					),
				},
			};
		case EDIT_MODULE:
			return {
				...state,
				moduleEdits: {
					...state.moduleEdits,
					[ action.module ]: action.edit,
				},
			};
		case RESET_MODULE_EDITS:
			return {
				...state,
				moduleEdits: omit( state.moduleEdits, action.modules ),
			};
		case START_SAVING_MODULES:
			return {
				...state,
				savingModules: [ ...state.savingModules, ...action.modules ],
			};
		case FINISH_SAVING_MODULES:
			return {
				...state,
				savingModules: state.savingModules.filter(
					( module ) => ! action.modules.includes( module )
				),
				moduleEdits: omit( state.moduleEdits, action.modules ),
				errors: omit( state.errors, action.modules ),
			};
		case FAILED_SAVING_MODULES:
			return {
				...state,
				savingModules: without(
					state.savingModules,
					...Object.keys( action.errors || {} ),
					...( action.modules || [] )
				),
				errors: {
					...state.errors,
					...action.errors,
				},
			};
		case RECEIVE_SETTINGS:
			return {
				...state,
				settings: {
					...state.settings,
					[ action.module ]: action.settings,
				},
			};
		case EDIT_SETTINGS:
			return {
				...state,
				settingEdits: {
					...state.settingEdits,
					[ action.module ]: action.edit,
				},
			};
		case EDIT_SETTING:
			return {
				...state,
				settingEdits: {
					...state.settingEdits,
					[ action.module ]: {
						...( state.settingEdits[ action.module ] || {} ),
						[ action.setting ]: action.value,
					},
				},
			};
		case RESET_SETTING_EDIT:
			return {
				...state,
				settingEdits: {
					...state.settingEdits,
					[ action.module ]: omit(
						state.settingEdits[ action.module ] || {},
						action.setting
					),
				},
			};
		case RESET_SETTING_EDITS:
			return {
				...state,
				settingEdits: omit( state.settingEdits, action.modules ),
			};
		case START_SAVING_SETTINGS:
			return {
				...state,
				savingSettings: [ ...state.savingSettings, ...action.modules ],
			};
		case FINISH_SAVING_SETTINGS:
			return {
				...state,
				savingSettings: state.savingSettings.filter(
					( module ) => ! action.modules.includes( module )
				),
				settingEdits: omit( state.settingEdits, action.modules ),
				errors: omit( state.errors, action.modules ),
			};
		case FAILED_SAVING_SETTINGS:
			return {
				...state,
				savingSettings: without(
					state.savingSettings,
					...Object.keys( action.errors || {} ),
					...( action.modules || [] )
				),
				errors: {
					...state.errors,
					...action.errors,
				},
			};
		default:
			return state;
	}
}
