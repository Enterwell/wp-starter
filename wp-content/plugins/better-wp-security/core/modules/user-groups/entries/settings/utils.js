/**
 * External dependencies
 */
import { filter, intersection, isPlainObject, map, reduce } from 'lodash';
import Ajv from 'ajv';
import { v4 as uuidv4 } from 'uuid';

/**
 * WordPress dependencies
 */
import { useDispatch, useRegistry } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ONBOARD_STORE_NAME } from '@ithemes/security.pages.settings';
import { store as uiStore } from '@ithemes/security.user-groups.ui';
import { STORE_NAME as SEARCH_STORE_NAME } from '@ithemes/security-search';
import { useSingletonEffect } from '@ithemes/security-hocs';
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import { store } from '@ithemes/security.user-groups.api';

function getAjv() {
	if ( ! getAjv.instance ) {
		getAjv.instance = new Ajv( { schemaId: 'id' } );
		getAjv.instance.addMetaSchema(
			require( 'ajv/lib/refs/json-schema-draft-04.json' )
		);
	}

	return getAjv.instance;
}

export function useApplyDefaultGroupSettings() {
	const { resolveSelect } = useRegistry();
	const { editGroupSetting } = useDispatch( uiStore );

	return async () => {
		const modules = await resolveSelect( MODULES_STORE_NAME ).getEditedModules();
		const groupIds = await resolveSelect( uiStore ).getMatchableNavIds();

		for ( const groupId of groupIds ) {
			for ( const module of modules ) {
				if ( module.status.selected !== 'active' ) {
					continue;
				}

				for ( const setting in module.user_groups ) {
					if ( ! module.user_groups.hasOwnProperty( setting ) ) {
						continue;
					}

					if ( module.user_groups[ setting ].default !== 'all' ) {
						continue;
					}

					editGroupSetting( groupId, module.id, setting, true );
				}
			}
		}
	};
}

export function useCreateDefaultGroups() {
	const { select, resolveSelect } = useRegistry();
	const { createLocalGroup, editGroup, editGroupSetting, createdDefaultGroups } = useDispatch( uiStore );

	return async () => {
		if ( select( uiStore ).hasCreatedDefaultGroups() ) {
			return select( uiStore ).getMatchableNavIds();
		}

		const defaultGroups = {
			administrator: __( 'Administrators', 'better-wp-security' ),
			editor: __( 'Editors', 'better-wp-security' ),
			author: __( 'Authors', 'better-wp-security' ),
			contributor: __( 'Contributors', 'better-wp-security' ),
			subscriber: __( 'Subscribers', 'better-wp-security' ),
		};

		const modules = await resolveSelect( MODULES_STORE_NAME ).getEditedModules();
		const matchables = await resolveSelect( store ).getMatchables();
		const localIds = select( uiStore ).getLocalGroupIds();
		const answers = select( ONBOARD_STORE_NAME ).getAnswers();

		const existing = {
			administrator: [],
			editor: [],
			author: [],
			contributor: [],
			subscriber: [],
		};

		for ( const groupId of map(
			filter( matchables, { type: 'user-group' } ),
			'id'
		).concat( localIds ) ) {
			const canonical = select( uiStore ).getEditedGroupAttribute(
				groupId,
				'canonical'
			);

			for ( const role of canonical ) {
				existing[ role ].push( groupId );
			}
		}

		const substitutions = {};

		for ( const answer of answers ) {
			if ( isPlainObject( answer.canonical_group_substitutions ) ) {
				Object.assign(
					substitutions,
					answer.canonical_group_substitutions
				);
			}
		}

		for ( const canonicalRole in defaultGroups ) {
			if ( ! defaultGroups.hasOwnProperty( canonicalRole ) ) {
				continue;
			}

			const ids = existing[ canonicalRole ];

			if ( substitutions.hasOwnProperty( canonicalRole ) ) {
				if ( null === substitutions[ canonicalRole ] && ! ids.length ) {
					continue;
				}

				ids.push( substitutions[ canonicalRole ] );
			}

			if ( ids.length === 0 ) {
				const id = uuidv4();
				createLocalGroup( id );
				editGroup( id, {
					label: defaultGroups[ canonicalRole ],
					canonical: [ canonicalRole ],
				} );
				ids.push( id );
			}

			if ( 'subscriber' === canonicalRole ) {
				ids.push( 'everybody-else' );
			}

			for ( const module of modules ) {
				if ( module.status.selected !== 'active' ) {
					continue;
				}

				for ( const setting in module.user_groups ) {
					if ( ! module.user_groups.hasOwnProperty( setting ) ) {
						continue;
					}

					if ( ! module.user_groups[ setting ].default ) {
						continue;
					}

					let settingDefault = module.user_groups[ setting ].default;

					if ( ! Array.isArray( settingDefault ) ) {
						settingDefault = [ settingDefault ];
					}

					if (
						intersection( [ 'all', canonicalRole ], settingDefault )
							.length > 0
					) {
						for ( const id of ids ) {
							editGroupSetting( id, module.id, setting, true );
						}
					}
				}
			}

			for ( const answer of answers ) {
				if ( ! answer.user_groups_settings[ canonicalRole ] ) {
					continue;
				}

				for ( const module in answer.user_groups_settings[
					canonicalRole
				] ) {
					if (
						! answer.user_groups_settings[
							canonicalRole
						].hasOwnProperty( module )
					) {
						continue;
					}

					for ( const setting of answer.user_groups_settings[
						canonicalRole
					][ module ] ) {
						for ( const id of ids ) {
							editGroupSetting( id, module, setting, true );
						}
					}
				}
			}
		}

		createdDefaultGroups();

		return select( uiStore ).getMatchableNavIds();
	};
}

export function useCompletionSteps() {
	const { registerCompletionStep } = useDispatch( ONBOARD_STORE_NAME );
	const { saveGroups, saveGroupSettingsAsBatch } = useDispatch( uiStore );

	useSingletonEffect( useCompletionSteps, () => {
		registerCompletionStep( {
			id: 'savingUserGroups',
			label: __( 'Create User Groups', 'better-wp-security' ),
			priority: 15,
			callback() {
				return saveGroups();
			},
		} );
		registerCompletionStep( {
			id: 'savingUserGroupsSetting',
			label: __( 'Setup User Group Settings', 'better-wp-security' ),
			priority: 20,
			callback() {
				return saveGroupSettingsAsBatch();
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
					.select( store )
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
						.select( uiStore )
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
