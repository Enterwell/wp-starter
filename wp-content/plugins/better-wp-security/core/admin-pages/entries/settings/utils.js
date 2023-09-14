/**
 * External dependencies
 */
import { useHistory, useLocation, useRouteMatch } from 'react-router-dom';
import { createLocation } from 'history';
import { pickBy, get, set } from 'lodash';
import Ajv from 'ajv';
import classnames from 'classnames';
import { JsonPointer } from 'json-ptr';

/**
 * WordPress dependencies
 */
import {
	createContext,
	useCallback,
	useContext,
	useMemo,
} from '@wordpress/element';
import { useDispatch, useSelect, useRegistry } from '@wordpress/data';
import { applyFilters } from '@wordpress/hooks';
import { __, _n, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { WPError } from '@ithemes/security-utils';
import {
	CORE_STORE_NAME,
	MODULES_STORE_NAME,
} from '@ithemes/security.packages.data';

export const ConfigContext = createContext( {
	serverType: '',
	installType: '',
	onboardComplete: false,
} );

export function useConfigContext() {
	return useContext( ConfigContext );
}

export function useNavigateTo() {
	const history = useHistory();

	return useCallback(
		( route, mode = 'push' ) => history[ mode ]( createLocation( route ) ),
		[ history ]
	);
}

/**
 * Gets the child path of the current route.
 *
 * For instance, if `/configure/settings/advanced` is the current URL,
 * and `/configure/settings` is the current route, then `/advanced` will be returned.
 *
 * @return {string} The current child path.
 */
export function useChildPath() {
	const { url } = useRouteMatch();
	const { pathname: locationPath } = useLocation();
	const { pathname: matchedPath } = createLocation( url );

	return locationPath.replace( matchedPath, '' );
}

/**
 * Grabs a global instance of Ajv.
 *
 * @return {Ajv.Ajv} The ajv instance.
 */
export function getAjv() {
	if ( ! getAjv.instance ) {
		getAjv.instance = new Ajv( { schemaId: 'id' } );
		getAjv.instance.addMetaSchema(
			require( 'ajv/lib/refs/json-schema-draft-04.json' )
		);
		getAjv.instance.addFormat( 'html', {
			type: 'string',
			validate() {
				// Validating HTML isn't something we can realistically do.
				// We accept everything and can then kses it on the server.
				return true;
			},
		} );
	}

	return getAjv.instance;
}

const isConditionalSettingActive = ( definition, module, context ) => {
	const {
		serverType,
		installType,
		activeModules,
		settings,
		featureFlags,
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

	/**
	 * Filters whether a conditional setting is active.
	 *
	 * This hook can only be used to turn a conditional setting inactive,
	 * if it is inactive due to other conditional rules, this filter won't run.
	 *
	 * @param {boolean} isActive   Whether the setting is active.
	 * @param {Object}  module     The module definition.
	 * @param {Object}  definition The conditional setting definition.
	 * @param {Object}  context    Context used to determine whether the setting is active.
	 */
	return applyFilters(
		'ithemes-security.settings.isConditionalSettingActive',
		true,
		module,
		definition,
		context
	);
};

/**
 * Makes a settings schema conditional based on the module definition.
 *
 * @param {Object}        module                The module definition.
 * @param {Object}        context               The context used to evaluate the conditional settings.
 * @param {string}        context.serverType    The web server type.
 * @param {string}        context.installType   The ITSEC installation type.
 * @param {Array<string>} context.activeModules The list of active modules.
 * @param {Array<string>} context.featureFlags  The list of feature flags.
 * @param {Object}        context.settings      The module's setting value.
 * @param {Object}        context.registry      The @wordpress/data registry.
 *
 * @return {Object} The settings schema.
 */
export function makeConditionalSettingsSchema( module, context ) {
	const isActive = ( definition ) =>
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
						! isActive(
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
			! isActive( module.settings.conditional[ propName ] )
		) {
			return acc;
		}

		acc[ propName ] = reduceConditional( propName, propSchema );

		return acc;
	}, {} );

	return {
		...module.settings.schema,
		properties,
	};
}

export function useConditionalSchema( module, settings ) {
	const { serverType, installType } = useConfigContext();
	const registry = useRegistry();
	const { activeModules, featureFlags } = useSelect(
		( select ) => ( {
			activeModules: select( MODULES_STORE_NAME ).getActiveModules(),
			featureFlags: select( CORE_STORE_NAME ).getFeatureFlags(),
		} ),
		[]
	);
	const context = {
		serverType,
		installType,
		activeModules,
		settings,
		registry,
		featureFlags,
	};

	if ( ! module ) {
		return null;
	}

	return makeConditionalSettingsSchema( module, context );
}

/**
 * A hook to allow for convenient editing of module settings.
 *
 * @param {Object}                            module         The module definition.
 * @param {function(Object, string): boolean} [filterFields] An optional function to filter the included settings.
 * @return {{schema: Object, uiSchema: Object, setFormData: Function, formData: Object}} The settings form components.
 */
export function useSettingsForm( module, filterFields ) {
	const formData = useSelect(
		( select ) =>
			select( MODULES_STORE_NAME ).getEditedSettings( module.id ),
		[ module.id ]
	);
	const { editSettings } = useDispatch( MODULES_STORE_NAME );
	const conditionalSchema = useConditionalSchema( module, formData );

	if ( filterFields ) {
		conditionalSchema.properties = pickBy(
			conditionalSchema.properties,
			filterFields
		);
	}

	const setFormData = ( e ) => {
		editSettings( module.id, e.formData );
	};

	return {
		schema: conditionalSchema,
		uiSchema: module.settings.schema.uiSchema,
		formData,
		setFormData,
	};
}

export function useModuleSchemaValidator( moduleId ) {
	const ajv = getAjv();
	const { module, settings } = useSelect(
		( select ) => ( {
			module: select( MODULES_STORE_NAME ).getModule( moduleId ),
			settings: select( MODULES_STORE_NAME ).getEditedSettings(
				moduleId
			),
		} ),
		[ moduleId ]
	);
	const conditionalSchema = useConditionalSchema( module, settings );
	const noOpTrue = useCallback( () => true );

	const compiled = useMemo( () => {
		if ( ! conditionalSchema ) {
			return noOpTrue;
		}

		return ajv.compile( conditionalSchema );
	}, [ conditionalSchema ] );

	return useCallback( () => {
		if ( compiled( settings ) ) {
			return true;
		}

		return {
			errors: compiled.errors,
			errorText: convertSchemaErrorToText(
				compiled.errors,
				moduleId,
				conditionalSchema
			),
		};
	}, [ compiled, settings ] );
}

function convertSchemaErrorToText( errors, moduleId, schema ) {
	const text = [];

	for ( const { message, schemaPath, dataPath } of errors ) {
		let ptr = JsonPointer.create( schemaPath );
		let parent = ptr.parent( schema );

		while ( parent && ! parent.title ) {
			ptr = JsonPointer.create(
				ptr.path.slice( 0, ptr.path.length - 1 )
			);
			parent = ptr.parent( schema );
		}

		if ( parent?.title ) {
			text.push( `${ parent.title } ${ message }.` );
		} else {
			text.push( `${ moduleId }${ dataPath } ${ message }.` );
		}
	}

	return text;
}

/**
 * Gets the list of module types.
 *
 * @return {({label: string, slug: string})[]} The list of module types.
 */
export function getModuleTypes() {
	return [
		{
			slug: 'login',
			label: __( 'Login Security', 'better-wp-security' ),
		},
		{
			slug: 'lockout',
			label: __( 'Lockouts', 'better-wp-security' ),
		},
		{
			slug: 'site-check',
			label: __( 'Site Check', 'better-wp-security' ),
		},
		{
			slug: 'utility',
			label: __( 'Utilities', 'better-wp-security' ),
		},
		{
			slug: 'advanced',
			label: __( 'Advanced', 'better-wp-security' ),
		},
	];
}

export function useModuleRequirementsValidator() {
	const { featureFlags, siteInfo, requirementsInfo } = useSelect(
		( select ) => ( {
			featureFlags: select( CORE_STORE_NAME ).getFeatureFlags(),
			siteInfo: select( CORE_STORE_NAME ).getSiteInfo(),
			requirementsInfo: select( CORE_STORE_NAME ).getRequirementsInfo(),
		} ),
		[]
	);

	const isVersionAtLeast = ( version, atLeast ) => {
		return version.localeCompare( atLeast, undefined, { numeric: true, sensitivity: 'base' } ) >= 0;
	};

	return useCallback(
		( module, mode ) => {
			const error = new WPError();

			if ( ! module.requirements ) {
				return error;
			}

			const isForMode = ( requirement ) =>
				requirement.validate === mode || mode === 'activate';

			if (
				module.requirements.ssl &&
				isForMode( module.requirements.ssl ) &&
				document.location.protocol !== 'https:'
			) {
				error.add( 'ssl', __( 'Your site must support SSL.', 'better-wp-security' ), module.requirements.ssl );
			}

			if (
				module.requirements[ 'feature-flags' ] &&
				isForMode( module.requirements[ 'feature-flags' ] )
			) {
				for ( const flag of module.requirements[ 'feature-flags' ]
					.flags ) {
					if ( ! featureFlags.includes( flag ) ) {
						error.add(
							'feature-flags',
							sprintf(
								/* translators: The name of the feature. */
								__(
									"The '%s' feature flag must be enabled.",
									'better-wp-security'
								),
								flag
							),
							module.requirements[ 'feature-flags' ]
						);
					}
				}
			}

			if ( module.requirements.multisite && isForMode( module.requirements.multisite ) ) {
				if ( module.requirements.multisite.status === 'enabled' && siteInfo?.multisite === false ) {
					error.add( 'multisite', __( 'Multisite must be enabled.', 'better-wp-security' ), module.requirements.multisite );
				} else if ( module.requirements.multisite.status === 'disabled' && siteInfo?.multisite === true ) {
					error.add( 'multisite', __( 'Multisite is not supported.', 'better-wp-security' ), module.requirements.multisite );
				}
			}

			if ( module.requirements.server && isForMode( module.requirements.server ) && requirementsInfo ) {
				if ( module.requirements.server.php && ! isVersionAtLeast( requirementsInfo.server.php, module.requirements.server.php ) ) {
					error.add( 'server', sprintf(
						/* translators: The PHP version. */
						__( 'You must be running PHP version %s or later.', 'better-wp-security' ),
						module.requirements.server.php
					), module.requirements.server );
				}

				const missingExtensions = ( module.requirements.server.extensions || [] )
					.filter( ( extension ) => ! requirementsInfo.server.extensions[ extension ] );

				if ( missingExtensions.length === 1 ) {
					error.add( 'server', sprintf(
						/* translators: PHP Extension name. */
						__( 'The %s PHP extension is required.', 'better-wp-security' ),
						missingExtensions[ 0 ]
					), module.requirements.server );
				} else if ( missingExtensions.length > 0 ) {
					error.add( 'server', sprintf(
						/* translators: List of PHP extensions */
						_n(
							'The following PHP extension is required: %l.',
							'The following PHP extensions are required: %l.',
							missingExtensions.length,
							'better-wp-security'
						).replace( '%l', '%s' ),
						missingExtensions.join( ', ' )
					), module.requirements.server );
				}
			}

			return error;
		},
		[ featureFlags, siteInfo, requirementsInfo ]
	);
}

/**
 * Appends a classname to a property at an arbitrary depth.
 *
 * This method mutates the object.
 *
 * @param {Object}        object    The object to modify.
 * @param {Array<string>} path      The path at which to append it.
 * @param {string}        className The class to append.
 * @return {Object} The object.
 */
export function appendClassNameAtPath( object, path, className ) {
	set( object, path, classnames( get( object, path ), className ) );

	return object;
}
