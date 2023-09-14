/**
 * External dependencies
 */
import { isString, isEqual, map, isEmpty } from 'lodash';
import { updatedDiff } from 'deep-object-diff';

/**
 * WordPress dependencies
 */
import { controls } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { apiFetch, apiFetchBatch, createNotice } from '../controls';
import { STORE_NAME } from './';

export function* editModule( module, edit ) {
	const current = yield controls.select( STORE_NAME, 'getModule', module );

	if ( current ) {
		yield { type: EDIT_MODULE, module, edit: updatedDiff( current, edit ) };
	} else {
		yield { type: EDIT_MODULE, module, edit };
	}
}

/**
 * Saves an edited module.
 *
 * @param {boolean|string|Array<string>} modules The modules to save. By default, all dirty modules will be saved.
 * @return {Array<Object>} The list of saved modules responses.
 */
export function* saveModules( modules = true ) {
	if ( modules === true ) {
		modules = yield controls.select( STORE_NAME, 'getDirtyModules' );
	} else if ( isString( modules ) ) {
		modules = [ modules ];
	}

	if ( ! modules.length ) {
		return [];
	}

	const requests = [];

	for ( const module of modules ) {
		const edits = yield controls.select(
			STORE_NAME,
			'getModuleEdits',
			module
		);

		requests.push( {
			method: 'PUT',
			path: `/ithemes-security/v1/modules/${ module }`,
			body: edits,
		} );
	}

	let responses;

	try {
		yield { type: START_SAVING_MODULES, modules };
		responses = yield apiFetchBatch( requests );
	} catch ( error ) {
		yield { type: FAILED_SAVING_MODULES, modules };
		yield createNotice( 'error', error.message );

		return error;
	}

	const success = [];
	const errors = {};

	for ( let i = 0; i < requests.length; i++ ) {
		const module = modules[ i ];
		const response = responses[ i ];

		if ( response.status >= 400 ) {
			errors[ module ] = response.body;
		} else {
			success.push( module );
			yield receiveModule( response.body );
		}
	}

	if ( ! isEmpty( errors ) ) {
		yield { type: FAILED_SAVING_MODULES, errors };
	}

	if ( success.length ) {
		yield { type: FINISH_SAVING_MODULES, modules: success };
	}

	return responses;
}

/**
 * Resets the edits for a module.
 *
 * @param {boolean|string|Array<string>} modules The modules to reset. By default, all dirty modules will be reset.
 */
export function* resetModuleEdits( modules = true ) {
	if ( modules === true ) {
		modules = yield controls.select( STORE_NAME, 'getDirtyModules' );
	} else if ( isString( modules ) ) {
		modules = [ modules ];
	}

	yield {
		type: RESET_MODULE_EDITS,
		modules,
	};
}

export function* activateModule( module ) {
	try {
		const response = yield updateModule( module, 'active' );
		yield receiveModule( response );
		yield { type: FINISH_SAVING_MODULES, modules: [ module ] };

		if ( response.side_effects ) {
			yield fetchModules();
		}
	} catch ( error ) {
		yield { type: FAILED_SAVING_MODULES, errors: { [ module ]: error } };
	}
}

export function* deactivateModule( module ) {
	try {
		const response = yield updateModule( module, 'inactive' );
		yield receiveModule( response );
		yield { type: FINISH_SAVING_MODULES, modules: [ module ] };

		if ( response.side_effects ) {
			yield fetchModules();
		}
	} catch ( error ) {
		yield { type: FAILED_SAVING_MODULES, errors: { [ module ]: error } };
	}
}

export function* setModulesStatus( modules ) {
	const batch = {
		requests: map( modules, ( status, module ) => ( {
			path: `/ithemes-security/v1/modules/${ module }`,
			method: 'PUT',
			body: {
				status: {
					selected: status,
				},
			},
		} ) ),
	};

	const responses = yield apiFetchBatch( batch );

	for ( let i = 0; i < responses.length; i++ ) {
		const response = responses[ i ];

		if ( response.status >= 400 ) {
			yield createNotice( 'error', response.body.message );
		} else {
			yield receiveModule( response.body );
		}
	}
}

export function* editSettings( module, settings ) {
	const current = yield controls.select( STORE_NAME, 'getSettings', module );

	if ( ! current ) {
		yield { type: EDIT_SETTINGS, module, edit: settings };
		return;
	}

	const edit = {};
	let hasChanges = false;

	for ( const setting in settings ) {
		if ( ! settings.hasOwnProperty( setting ) ) {
			continue;
		}

		if ( ! isEqual( settings[ setting ], current[ setting ] ) ) {
			edit[ setting ] = settings[ setting ];
			hasChanges = true;
		}
	}

	if ( hasChanges ) {
		yield { type: EDIT_SETTINGS, module, edit };
	} else {
		yield resetSettingEdits( module );
	}
}

export function* editSetting( module, setting, value ) {
	const current = yield controls.select(
		STORE_NAME,
		'getSetting',
		module,
		setting
	);

	if ( isEqual( current, value ) ) {
		yield { type: RESET_SETTING_EDIT, module, setting };
	} else {
		yield {
			type: EDIT_SETTING,
			module,
			setting,
			value,
		};
	}
}

/**
 * Resets the edited settings for a module.
 *
 * @param {boolean|string|Array<string>} modules The modules to reset. By default, all dirty modules will be reset.
 */
export function* resetSettingEdits( modules = true ) {
	if ( modules === true ) {
		modules = yield controls.select( STORE_NAME, 'getDirtySettings' );
	} else if ( isString( modules ) ) {
		modules = [ modules ];
	}

	yield {
		type: RESET_SETTING_EDITS,
		modules,
	};
}

/**
 * Resets the edited settings for a module.
 *
 * @param {boolean|string|Array<string>} modules The modules to save. By default, all dirty modules will be saved.
 * @return {Array<Object>} The list of saved settings responses.
 */
export function* saveSettings( modules = true ) {
	if ( modules === true ) {
		modules = yield controls.select( STORE_NAME, 'getDirtySettings' );
	} else if ( isString( modules ) ) {
		modules = [ modules ];
	}

	if ( ! modules.length ) {
		return [];
	}

	const requests = [];

	for ( const module of modules ) {
		const settings = yield controls.select(
			STORE_NAME,
			'getSettingEdits',
			module
		);

		requests.push( {
			method: 'PATCH',
			path: `/ithemes-security/v1/settings/${ module }`,
			body: settings,
		} );
	}

	let responses;

	try {
		yield { type: START_SAVING_SETTINGS, modules };
		responses = yield apiFetchBatch( requests );
	} catch ( error ) {
		yield { type: FAILED_SAVING_SETTINGS, modules };
		yield createNotice( 'error', error.message );

		return error;
	}

	const success = [];
	const errors = {};

	for ( let i = 0; i < requests.length; i++ ) {
		const module = modules[ i ];
		const response = responses[ i ];

		if ( response.status >= 400 ) {
			errors[ module ] = response.body;
		} else {
			success.push( module );
			yield receiveSettings( module, response.body );
		}
	}

	if ( ! isEmpty( errors ) ) {
		yield { type: FAILED_SAVING_SETTINGS, errors };
	}

	if ( success.length ) {
		yield createNotice( 'success', __( 'Saved settings.', 'better-wp-security' ), {
			type: 'snackbar',
		} );
		yield { type: FINISH_SAVING_SETTINGS, modules: success };
	}

	return responses;
}

export function* updateSettings( module, settings ) {
	yield { type: START_SAVING_SETTINGS, modules: [ module ] };

	let response;

	try {
		response = yield apiFetch( {
			path: `/ithemes-security/v1/settings/${ module }`,
			method: 'PATCH',
			data: settings,
		} );
		yield receiveSettings( module, response );
	} catch ( error ) {
		yield { type: FAILED_SAVING_SETTINGS, errors: { [ module ]: error } };

		return error;
	}

	yield { type: FINISH_SAVING_SETTINGS, modules: [ module ] };

	return response;
}

function updateModule( module, status ) {
	return apiFetch( {
		method: 'PUT',
		path: `/ithemes-security/v1/modules/${ module }`,
		data: {
			status: {
				selected: status,
			},
		},
	} );
}

export function* fetchModules() {
	const modules = yield apiFetch( {
		path: '/ithemes-security/v1/modules?context=edit&_embed=1',
	} );
	yield receiveModules( modules );
}

export function receiveModules( modules ) {
	return {
		type: RECEIVE_MODULES,
		modules,
	};
}

export function receiveModule( module ) {
	return {
		type: RECEIVE_MODULE,
		module,
	};
}

export function receiveSettings( module, settings ) {
	return {
		type: RECEIVE_SETTINGS,
		module,
		settings,
	};
}

export const RECEIVE_MODULES = 'RECEIVE_MODULES';
export const RECEIVE_MODULE = 'RECEIVE_MODULE';
export const EDIT_MODULE = 'EDIT_MODULE';
export const RESET_MODULE_EDITS = 'RESET_MODULE_EDITS';

export const START_SAVING_MODULES = 'START_SAVING_MODULES';
export const FAILED_SAVING_MODULES = 'FAILED_SAVING_MODULES';
export const FINISH_SAVING_MODULES = 'FINISH_SAVING_MODULES';

export const RECEIVE_SETTINGS = 'RECEIVE_SETTINGS';
export const EDIT_SETTINGS = 'EDIT_SETTINGS';
export const EDIT_SETTING = 'EDIT_SETTING';
export const RESET_SETTING_EDIT = 'RESET_SETTING_EDIT';
export const RESET_SETTING_EDITS = 'RESET_SETTING_EDITS';

export const START_SAVING_SETTINGS = 'START_SAVING_SETTINGS';
export const FAILED_SAVING_SETTINGS = 'FAILED_SAVING_SETTINGS';
export const FINISH_SAVING_SETTINGS = 'FINISH_SAVING_SETTINGS';
