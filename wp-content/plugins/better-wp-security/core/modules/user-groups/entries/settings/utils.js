/**
 * External dependencies
 */
import { reduce, zipObject } from 'lodash';
import Ajv from 'ajv';

/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ONBOARD_STORE_NAME } from '@ithemes/security.pages.settings';
import { STORE_NAME as SEARCH_STORE_NAME } from '@ithemes/security-search';
import { useSingletonEffect } from '@ithemes/security-hocs';

function getAjv() {
	if ( ! getAjv.instance ) {
		getAjv.instance = new Ajv( { schemaId: 'id' } );
		getAjv.instance.addMetaSchema(
			require( 'ajv/lib/refs/json-schema-draft-04.json' )
		);
	}

	return getAjv.instance;
}

export function useSettingsDefinitions( filters = {} ) {
	const ajv = getAjv();

	return useSelect(
		( select ) =>
			select( 'ithemes-security/user-groups' ).getSettingDefinitions(
				ajv,
				filters
			),
		[ ajv, filters ]
	);
}

export function useCompletionSteps() {
	const { registerCompletionStep } = useDispatch( ONBOARD_STORE_NAME );
	const { saveGroups, saveGroupSettingsAsBatch } = useDispatch(
		'ithemes-security/user-groups-editor'
	);

	useSingletonEffect( useCompletionSteps, () => {
		registerCompletionStep( {
			id: 'savingUserGroups',
			label: __( 'Create User Groups', 'better-wp-security' ),
			priority: 15,
			callback() {
				return saveGroups();
			},
			render: function SavingUserGroups() {
				const groups = useSelect( ( select ) => {
					const store = select(
						'ithemes-security/user-groups-editor'
					);

					return ( store.getMatchableNavIds() || [] ).map( ( id ) =>
						store.getEditedMatchableLabel( id )
					);
				}, [] );

				if ( ! groups.length ) {
					return (
						<p>
							{ __(
								'No User Groups have been created.',
								'better-wp-security'
							) }
						</p>
					);
				}

				return (
					<>
						<p>
							{ __(
								'The following User Groups will be created:',
								'better-wp-security'
							) }
						</p>
						<ul>
							{ groups.map( ( group, i ) => (
								<li key={ i }>{ group }</li>
							) ) }
						</ul>
					</>
				);
			},
		} );
		registerCompletionStep( {
			id: 'savingUserGroupsSetting',
			label: __( 'Setup User Group Settings', 'better-wp-security' ),
			priority: 20,
			callback() {
				return saveGroupSettingsAsBatch();
			},
			render: function SavingUserGroupsSettings() {
				const definitions = useSettingsDefinitions();
				const { ids: groupIds, labels, settings } = useSelect(
					( select ) => {
						const store = select(
							'ithemes-security/user-groups-editor'
						);
						const ids = store.getMatchableNavIds() || [];

						return {
							ids,
							labels: zipObject(
								ids,
								ids.map( ( id ) =>
									store.getEditedMatchableLabel( id )
								)
							),
							settings: zipObject(
								ids,
								ids.map( ( id ) =>
									store.getEditedGroupSettings( id )
								)
							),
						};
					},
					[]
				);

				if ( ! groupIds.length ) {
					return (
						<p>
							{ __(
								'No User Groups have been created.',
								'better-wp-security'
							) }
						</p>
					);
				}

				return (
					<>
						<p>
							{ __(
								'The following features will be enabled for each User Group:',
								'better-wp-security'
							) }
						</p>
						<ul className="itsec-secure-site-user-groups-settings-panel">
							{ groupIds.map( ( id ) => {
								const settingLabels = definitions.flatMap(
									( module ) => {
										if ( ! settings[ id ]?.[ module.id ] ) {
											return [];
										}

										return reduce(
											module.settings,
											( acc, definition, setting ) => {
												if (
													settings[ id ][ module.id ][
														setting
													] === true
												) {
													acc.push(
														definition.title
													);
												}

												return acc;
											},
											[]
										);
									}
								);

								if ( ! settingLabels.length ) {
									return (
										<li key={ id }>
											{ sprintf(
												/* translators: 1. The User Group label. */
												__( '%s: None', 'better-wp-security' ),
												labels[ id ]
											) }
										</li>
									);
								}

								return (
									<li key={ id }>
										<strong>{ labels[ id ] }</strong>
										<ul>
											{ settingLabels.map(
												( label, i ) => (
													<li key={ i }>{ label }</li>
												)
											) }
										</ul>
									</li>
								);
							} ) }
						</ul>
					</>
				);
			},
		} );
	} );
}

export function useSearchProviders() {
	const { registerProvider } = useDispatch( SEARCH_STORE_NAME );

	useSingletonEffect( useSearchProviders, () => {
		registerProvider(
			'user-group-settings',
			__( 'User Group Settings', 'better-wp-security' ),
			25,
			( { registry, evaluate, results } ) => {
				const definitions = registry
					.select( 'ithemes-security/user-groups' )
					.getSettingDefinitions( getAjv() );

				return definitions.reduce(
					( total, module ) =>
						reduce(
							module.settings,
							( count, config, group ) => {
								if (
									! evaluate.stringMatch( config.title ) &&
									! evaluate.stringMatch(
										config.description
									) &&
									! evaluate.keywordMatch( config.keywords )
								) {
									return count;
								}

								results.groups[ module.id ] ??= {
									title: module.title,
									items: [],
								};

								results.groups[ module.id ].items.push( {
									title: config.title,
									description: config.description,
									route: `/settings/user-groups?module=${ module.id }#${ module.id }/${ group }`,
								} );

								return count++;
							},
							total
						),
					0
				);
			}
		);

		registerProvider(
			'user-groups',
			__( 'User Groups', 'better-wp-security' ),
			50,
			( { registry, evaluate, results } ) => {
				const groups =
					registry
						.select( 'ithemes-security/user-groups-editor' )
						.getAvailableGroups() || [];

				return groups.reduce( ( count, group ) => {
					if ( ! evaluate.stringMatch( group.label ) ) {
						return count;
					}

					results.items.push( {
						title: group.label,
						description: group.description,
						route: `/settings/user-groups/${ group.id }`,
					} );

					return count++;
				}, 0 );
			}
		);
	} );
}
