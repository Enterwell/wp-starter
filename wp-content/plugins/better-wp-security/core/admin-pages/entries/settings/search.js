/**
 * WordPress dependencies
 */
import { useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { useSingletonEffect } from '@ithemes/security-hocs';
import { STORE_NAME } from '@ithemes/security-search';
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';

export default function useSearchProviders() {
	const { registerProvider } = useDispatch( STORE_NAME );

	useSingletonEffect( useSearchProviders, () => {
		registerProvider(
			'modules',
			__( 'Features', 'better-wp-security' ),
			5,
			( { registry, evaluate, results } ) => {
				const modules = registry
					.select( MODULES_STORE_NAME )
					.getEditedModules();

				return modules.reduce( ( count, module ) => {
					const moduleRoute = getModuleRoute( module );

					if ( ! moduleRoute ) {
						return count;
					}

					if (
						! evaluate.stringMatch( module.title ) &&
						! evaluate.stringMatch( module.description ) &&
						! evaluate.keywordMatch( module.keywords )
					) {
						return count;
					}

					results.items.push( {
						title: module.title,
						description: module.description,
						route: moduleRoute,
					} );

					return count + 1;
				}, 0 );
			}
		);

		registerProvider(
			'settings',
			__( 'Settings', 'better-wp-security' ),
			20,
			( { registry, evaluate, results } ) => {
				const modules = registry
					.select( MODULES_STORE_NAME )
					.getEditedModules();

				return modules.reduce( ( total, module ) => {
					if (
						module.status.selected !== 'active' ||
						! module.settings?.interactive?.length
					) {
						return total;
					}

					const moduleRoute = getModuleRoute( module );

					if ( ! moduleRoute ) {
						return total;
					}

					return (
						total +
						module.settings.interactive.reduce(
							( count, setting ) => {
								const schema =
									module.settings.schema.properties[
										setting
									];
								const uiSchema =
									module.settings.schema.uiSchema?.[
										setting
									];

								if ( ! schema ) {
									return count;
								}

								const title =
									uiSchema?.ui?.title ||
									uiSchema?.[ 'ui:title' ] ||
									schema.title;
								const description =
									uiSchema?.ui?.description ||
									uiSchema?.[ 'ui:description' ] ||
									schema.description;

								if (
									! evaluate.stringMatch( title ) &&
									! evaluate.stringMatch( description ) &&
									! evaluate.keywordMatch( schema.keywords )
								) {
									return count;
								}

								results.groups[ module.id ] ??= {
									title: module.title,
									items: [],
								};

								const route = moduleRoute.includes( '#' )
									? `${ moduleRoute },${ setting }`
									: `${ moduleRoute }#${ setting }`;

								results.groups[ module.id ].items.push( {
									title,
									description,
									route,
								} );

								return count++;
							},
							0
						)
					);
				}, 0 );
			}
		);
	} );
}

function getModuleRoute( module ) {
	if ( module.id === 'global' ) {
		return '/settings/global';
	}

	if ( module.type === 'custom' || module.type === 'tool' || module.type === 'recommended' ) {
		return;
	}

	if ( module.type === 'advanced' ) {
		return `/settings/advanced#${ module.id }`;
	}

	if ( module.status.default === 'always-active' && ! module.settings?.show_ui ) {
		return;
	}

	return `/settings/configure/${ module.type }#${ module.id }`;
}
