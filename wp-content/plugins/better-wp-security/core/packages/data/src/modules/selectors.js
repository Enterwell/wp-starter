/**
 * External dependencies
 */
import createSelector from 'rememo';
import { reduce, isEmpty } from 'lodash';
import memize from 'memize';

/**
 * WordPress dependencies
 */
import { createRegistrySelector } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { getAjv } from '@ithemes/security-utils';
import { STORE_NAME as CORE_STORE_NAME } from '../constant';

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
	( state, module ) => {
		const rawModule = getModule( state, module );

		if ( ! rawModule ) {
			return null;
		}

		return {
			...rawModule,
			...( state.moduleEdits[ module ] || {} ),
		};
	},
	( state, module ) => [
		state.modules,
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

export function isSavingSettings( state, moduleOrModules ) {
	if ( Array.isArray( moduleOrModules ) ) {
		return state.savingSettings.some( ( module ) => moduleOrModules.includes( module ) );
	}
	return state.savingSettings.includes( moduleOrModules );
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

const isConditionalSettingActive = ( definition, module, context ) => {
	const {
		serverType,
		installType,
		activeModules,
		settings,
		featureFlags,
		userGroupsBySetting,
	} = context;

	if (
		definition[ 'server-type' ] &&
		! definition[ 'server-type' ].includes( serverType )
	) {
		return false;
	}

	if (
		definition[ 'install-type' ] &&
		definition[ 'install-type' ] !== installType
	) {
		return false;
	}

	if ( definition[ 'active-modules' ] ) {
		for ( const activeModule of definition[ 'active-modules' ] ) {
			if ( ! activeModules.includes( activeModule ) ) {
				return false;
			}
		}
	}

	if ( definition[ 'user-groups' ] ) {
		for ( const userGroupSetting of definition[ 'user-groups' ] ) {
			if ( ! userGroupsBySetting[ module.id ]?.[ userGroupSetting ]?.length ) {
				return false;
			}
		}
	}

	if ( definition[ 'feature-flags' ] ) {
		for ( const featureFlag of definition[ 'feature-flags' ] ) {
			if ( ! featureFlags?.includes( featureFlag ) ) {
				return false;
			}
		}
	}

	if ( definition.settings ) {
		const ajv = getAjv();
		const validate = ajv.compile( definition.settings );

		if ( ! validate( settings ) ) {
			return false;
		}
	}

	return true;
};

const makeConditionalSettingsSchema = memize( (
	module,
	select,
	serverType,
	installType,
	featureFlags,
	activeModules,
	settings,
	userGroupsBySetting,
) => {
	const context = {
		select,
		serverType,
		installType,
		featureFlags,
		activeModules,
		settings,
		userGroupsBySetting,
	};

	const isActiveForDefinition = ( definition ) =>
		isConditionalSettingActive( definition, module, context );
	const reduceConditional = ( parent, subSchema ) => {
		if ( ! subSchema.properties ) {
			return subSchema;
		}

		return {
			...subSchema,
			properties: Object.entries( subSchema.properties ).reduce(
				( acc, [ propName, propSchema ] ) => {
					const conditionalKey = `${ parent }.${ propName }`;

					if (
						module.settings.conditional[ conditionalKey ] &&
						! isActiveForDefinition(
							module.settings.conditional[ conditionalKey ]
						)
					) {
						return acc;
					}

					acc[ propName ] = reduceConditional(
						conditionalKey,
						propSchema
					);

					return acc;
				},
				{}
			),
		};
	};

	const properties = Object.entries(
		module.settings.schema.properties
	).reduce( ( acc, [ propName, propSchema ] ) => {
		if ( ! module.settings.interactive.includes( propName ) ) {
			return acc;
		}

		if (
			module.settings.conditional[ propName ] &&
			! isActiveForDefinition( module.settings.conditional[ propName ] )
		) {
			return acc;
		}

		acc[ propName ] = reduceConditional( propName, propSchema );

		return acc;
	}, {} );

	const { id, ...rest } = module.settings.schema;

	return {
		...rest,
		properties,
	};
} );

export const getSettingsConditionalSchema = createRegistrySelector( ( select ) => ( state, moduleId ) => {
	const module = getEditedModule( state, moduleId );

	if ( ! module?.settings ) {
		return null;
	}

	return makeConditionalSettingsSchema(
		module,
		select,
		select( CORE_STORE_NAME ).getServerType(),
		select( CORE_STORE_NAME ).getInstallType(),
		select( CORE_STORE_NAME ).getFeatureFlags(),
		getActiveModules( state ),
		getEditedSettings( state, moduleId ),
		select( 'ithemes-security/user-groups-editor' ).getEditedGroupsBySetting(),
	);
} );
