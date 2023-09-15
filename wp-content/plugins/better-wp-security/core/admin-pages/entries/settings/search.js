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
import { TOOLS_STORE_NAME } from './stores';

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

								results.groups[ module.id ].items.push( {
									title,
									description,
									route: `${ moduleRoute }#${ setting }`,
								} );

								return count++;
							},
							0
						)
					);
				}, 0 );
			}
		);

		registerProvider(
			'tools',
			__( 'Tools', 'better-wp-security' ),
			100,
			( { registry, evaluate, results } ) => {
				const tools = registry
					.select( TOOLS_STORE_NAME )
					.getResolvedTools();

				return tools.reduce( ( total, tool ) => {
					if ( ! tool.available ) {
						return total;
					}

					if (
						! evaluate.stringMatch( tool.title ) &&
						! evaluate.stringMatch( tool.description ) &&
						! evaluate.keywordMatch( tool.keywords )
					) {
						return total;
					}

					results.items.push( {
						title: tool.title,
						description: tool.description,
						route: `/settings/tools#${ tool.slug }`,
					} );

					return total + 1;
				}, 0 );
			}
		);
	} );
}

function getModuleRoute( module ) {
	if ( module.type === 'custom' || module.type === 'tool' ) {
		return;
	}

	if ( module.id === 'password-requirements' ) {
		return '/settings/user-groups?module=password-requirements';
	}

	const featureLink = `/settings/modules/${ module.type }#${ module.id }`;

	if ( module.status.selected === 'inactive' ) {
		return featureLink;
	}

	if ( module.settings?.interactive.length > 0 ) {
		return module.type === 'recommended'
			? `/settings/configure/${ module.id }`
			: `/settings/configure/${ module.type }/${ module.id }`;
	}

	if ( module.status.default !== 'always-active' ) {
		return featureLink;
	}
}
