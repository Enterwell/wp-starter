/**
 * External dependencies
 */
import { useHistory, useLocation, useParams, useRouteMatch, Link } from 'react-router-dom';
import { createLocation } from 'history';
import { pickBy, get, set, isEmpty, every } from 'lodash';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import {
	createContext,
	createInterpolateElement,
	useCallback,
	useContext,
	useMemo,
} from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { __, _n, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { useGlobalNavigationUrl, WPError } from '@ithemes/security-utils';
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
 * A hook to allow for convenient editing of module settings.
 *
 * @param {Object}                            module         The module definition.
 * @param {function(Object, string): boolean} [filterFields] An optional function to filter the included settings.
 * @return {{schema: Object, uiSchema: Object, hasSettings: boolean, setFormData: Function, formData: Object}} The settings form components.
 */
export function useSettingsForm( module, filterFields ) {
	const { formData, conditionalSchemaBase } = useSelect(
		( select ) => ( {
			formData: select( MODULES_STORE_NAME ).getEditedSettings( module.id ),
			conditionalSchemaBase: select( MODULES_STORE_NAME ).getSettingsConditionalSchema( module.id ),
		} ),
		[ module.id ]
	);
	const { editSettings } = useDispatch( MODULES_STORE_NAME );

	const conditionalSchema = filterFields ? {
		...conditionalSchemaBase,
		properties: pickBy(
			conditionalSchemaBase.properties,
			filterFields
		),
	} : conditionalSchemaBase;

	const hasSettings = ! every(
		conditionalSchema?.properties,
		( propSchema ) =>
			propSchema.type === 'object' &&
			isEmpty( propSchema.properties )
	);

	const setFormData = ( e ) => {
		editSettings( module.id, e.formData );
	};

	return {
		schema: conditionalSchema,
		uiSchema: module.settings?.schema.uiSchema,
		hasSettings,
		formData,
		setFormData,
	};
}

/**
 * A hook to retrieve the allowed settings for the current root.
 *
 * @param {Object} module The module definition.
 * @return {{allowedFields: Array<string>, filterFields: ((function(object, string): boolean))}} The list of allowed fields, and a filter callback.
 */
export function useAllowedSettingsFields( module ) {
	const { root } = useParams();

	const allowedFields = ( () => {
		switch ( root ) {
			case 'import':
				return module?.settings?.import;
			case 'onboard':
				return module?.settings?.onboard;
		}
	} )();
	const _filterFields = useCallback(
		( value, key ) => allowedFields.includes( key ),
		[ allowedFields ]
	);
	const filterFields = allowedFields && _filterFields;

	return {
		allowedFields,
		filterFields,
	};
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
			label: __( 'Firewall', 'better-wp-security' ),
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
	const { featureFlags, siteInfo, requirementsInfo, proxy } = useSelect(
		( select ) => ( {
			featureFlags: select( CORE_STORE_NAME ).getFeatureFlags(),
			siteInfo: select( CORE_STORE_NAME ).getSiteInfo(),
			requirementsInfo: select( CORE_STORE_NAME ).getRequirementsInfo(),
			proxy: select( MODULES_STORE_NAME ).getEditedSetting( 'global', 'proxy' ),
		} ),
		[]
	);

	const { root } = useParams();
	const proxyPath = root && `/${ root }/global#proxy`;
	const proxyUrl = useGlobalNavigationUrl( 'settings', '/settings/global' ) + '#proxy';

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

			if ( module.requirements.load && isForMode( module.requirements.load ) && requirementsInfo ) {
				if ( module.requirements.load.type === 'normal' && requirementsInfo.load === 'early' ) {
					error.add( 'load', __( 'Loading Solid Security via an MU-Plugin is not supported.', 'better-wp-security' ) );
				} else if ( module.requirements.load.type === 'early' && requirementsInfo.load === 'normal' ) {
					error.add( 'load', __( 'Loading Solid Security without an MU-Plugin is not supported.', 'better-wp-security' ) );
				}
			}

			if ( module.requirements.ip && isForMode( module.requirements.ip ) ) {
				if ( proxy === 'automatic' ) {
					error.add(
						'ip',
						createInterpolateElement(
							__( 'You must select an IP Detection method in <a>Global Settings</a>. <help>Learn more</help>.', 'better-wp-security' ),
							{
								// eslint-disable-next-line jsx-a11y/anchor-has-content
								a: proxyPath ? <Link to={ proxyPath } /> : <a href={ proxyUrl } />,
								// eslint-disable-next-line jsx-a11y/anchor-has-content
								help: <a href="https://go.solidwp.com/firewall-features-not-available" />,
							}
						),
						module.requirements.ip
					);
				}
			}

			return error;
		},
		[ featureFlags, siteInfo, requirementsInfo, proxy, proxyPath, proxyUrl ]
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

export function useHighlightedVulnerabilities( issues, count ) {
	const getSeverity = ( score ) => {
		if ( score < 3 ) {
			return 'low';
		}
		if ( score < 7 ) {
			return 'medium';
		}
		if ( score < 9 ) {
			return 'high';
		}
		return 'critical';
	};

	return useMemo( () => {
		const grouped = {};

		for ( const issue of issues ) {
			const key = `${ issue.software.type.slug }:${ issue.software.slug }`;

			if ( ! grouped[ key ] ) {
				grouped[ key ] = {
					software: issue.software,
					critical: 0,
					high: 0,
					medium: 0,
					low: 0,
					maxScore: 0,
				};
			}

			grouped[ key ][ getSeverity( issue.details.score ) ]++;

			if ( issue.details.score > grouped[ key ].maxScore ) {
				grouped[ key ].maxScore = issue.details.score;
			}
		}

		const sorted = Object.values( grouped );
		sorted.sort( ( a, b ) => b.maxScore - a.maxScore );
		const show = sorted.slice( 0, count );
		const remaining = sorted.slice( count ).reduce( ( acc, software ) => acc + software.critical + software.high + software.medium + software.low, 0 );

		return {
			show,
			remaining,
		};
	}, [ issues, count ] );
}
