/**
 * Lodash
 */
import { isEmpty } from 'lodash';

/**
 * WordPress dependencies
 */
import { controls } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { apiFetch } from '../controls';
import { fetchModules, receiveSettings } from './actions';
import { STORE_NAME } from './constant';

export function *getModules() {
	yield fetchModules();
}

export const getSettings = {
	*fulfill( module ) {
		const settings = yield apiFetch( {
			path: `/ithemes-security/v1/settings/${ module }`,
		} );
		yield receiveSettings( module, settings );
	},
	isFulfilled( state, module ) {
		return state.settings.hasOwnProperty( module );
	},
};

export const __unstableGetAllSettings = {
	*fulfill() {
		yield controls.resolveSelect( STORE_NAME, 'getModules' );
	},
	isFulfilled( state ) {
		return ! isEmpty( state.settings );
	},
};

export function* __unstableGetAllEditedSettings() {
	yield controls.resolveSelect( STORE_NAME, '__unstableGetAllSettings' );
}

export function* getEditedModules() {
	yield controls.resolveSelect( STORE_NAME, 'getModules' );
}

export const getModule = {
	*fulfill() {
		yield controls.resolveSelect( STORE_NAME, 'getModules' );
	},
	isFulfilled( state, module ) {
		return state.modules.includes(
			( maybeModule ) => maybeModule.id === module
		);
	},
};

export function* getEditedModule() {
	yield controls.resolveSelect( STORE_NAME, 'getModules' );
}

export function* getActiveModules() {
	yield controls.resolveSelect( STORE_NAME, 'getModules' );
}

export function* getSetting( module ) {
	yield controls.resolveSelect( STORE_NAME, 'getSettings', module );
}

export function* getEditedSettings( module ) {
	yield controls.resolveSelect( STORE_NAME, 'getSettings', module );
}

export function* getEditedSetting( module ) {
	yield controls.resolveSelect( STORE_NAME, 'getSettings', module );
}
