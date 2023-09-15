/**
 * External dependencies
 */
import createSelector from 'rememo';
import { reduce, isEmpty } from 'lodash';

export function getModules( state ) {
	return state.modules;
}

export const getEditedModules = createSelector(
	( state ) =>
		state.modules.map( ( module ) => ( {
			...module,
			...( state.moduleEdits[ module.id ] || {} ),
		} ) ),
	( state ) => [ state.modules, state.moduleEdits ]
);

export const getEditedModule = createSelector(
	( state, module ) => ( {
		...( state.modules[ module ] || {} ),
		...( state.moduleEdits[ module ] || {} ),
	} ),
	( state, module ) => [
		state.modules[ module ],
		state.moduleEdits[ module ],
	]
);

export const getActiveModules = createSelector(
	( state ) =>
		state.modules
			.filter( ( module ) => module.status.selected === 'active' )
			.map( ( module ) => module.id ),
	( state ) => state.modules
);

export function getModule( state, module ) {
	return state.modules.find( ( maybe ) => maybe.id === module );
}

export function isActive( state, module ) {
	return getModule( state, module )?.status.selected === 'active';
}

export function getModuleEdits( state, module ) {
	return state.moduleEdits[ module ];
}

export function isSavingModule( state, module ) {
	return state.savingModules.includes( module );
}

export function getDirtyModules( state ) {
	return Object.keys( state.moduleEdits );
}

export function isModuleDirty( state, module ) {
	return !! state.moduleEdits[ module ];
}

export function getSettings( state, module ) {
	return state.settings[ module ] ?? {};
}

export function getSetting( state, module, setting ) {
	return state.settings[ module ]?.[ setting ];
}

export function getSettingEdits( state, module ) {
	return state.settingEdits[ module ];
}

export const getEditedSettings = createSelector(
	( state, module ) => ( {
		...( state.settings[ module ] || {} ),
		...( state.settingEdits[ module ] || {} ),
	} ),
	( state, module ) => [
		state.settings[ module ],
		state.settingEdits[ module ],
	]
);

export function getEditedSetting( state, module, setting ) {
	return (
		state.settingEdits[ module ]?.[ setting ] ||
		state.settings[ module ]?.[ setting ]
	);
}

export function isSavingSettings( state, module ) {
	return state.savingSettings.includes( module );
}

export function getDirtySettings( state ) {
	return Object.keys( state.settingEdits );
}

export function areSettingsDirty( state, module ) {
	return (
		state.settingEdits[ module ] &&
		! isEmpty( state.settingEdits[ module ] )
	);
}

export function getError( state, module ) {
	return state.errors[ module ];
}

export function __unstableGetAllSettings( state ) {
	return state.settings;
}

export const __unstableGetAllEditedSettings = createSelector(
	( state ) =>
		reduce(
			state.settings,
			( acc, settings, module ) => {
				acc[ module ] = {
					...settings,
					...( state.settingEdits[ module ] || {} ),
				};

				return acc;
			},
			{}
		),
	( state ) => [ state.settings, state.settingEdits ]
);

export function getSettingSchema( state, module, setting ) {
	return getModule( state, module )?.settings?.schema.properties[ setting ];
}
