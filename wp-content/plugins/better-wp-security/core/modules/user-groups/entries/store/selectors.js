/**
 * External dependencies
 */
import createSelector from 'rememo';
import { map, filter, isObject, get, isEmpty, pickBy } from 'lodash';
import memize from 'memize';

/**
 * WordPress dependencies
 */
import { createRegistrySelector } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';

/**
 * Get the list of matchables.
 *
 * @param {Object} state
 * @return {Array<Object>}
 */
export const getMatchables = createSelector(
	( state ) =>
		filter(
			map( state.matchableIds, ( id ) => state.matchablesById[ id ] ),
			isObject
		),
	( state ) => [ state.matchablesById, state.matchableIds ]
);

/**
 * Gets the type of a matchable.
 *
 * @param {Object} state Store state.
 * @param {string} id    Matchable id.
 *
 * @return {string} Either 'user-group' or 'meta'.
 */
export function getMatchableType( state, id ) {
	return ( state.matchablesById[ id ] || {} ).type;
}

/**
 * Gets the label for a matchable.
 *
 * @param {Object} state Store state.
 * @param {string} id    Matchable id.
 *
 * @return {string} The matchable's label.
 */
export function getMatchableLabel( state, id ) {
	return ( state.matchablesById[ id ] || {} ).label;
}

/**
 * Returns all the groups returned by a query ID.
 *
 * @param {Object} state   Data state.
 * @param {string} queryId Query ID.
 *
 * @return {Array} Groups list.
 */
export const getGroups = createSelector(
	( state, queryId ) =>
		filter(
			map( state.queries[ queryId ], ( id ) => state.byId[ id ] ),
			isObject
		),
	( state, queryId ) => [ state.queries[ queryId ], state.byId ]
);

const UNKNOWN_QUERIED_OBJECTS = [];

/**
 * Get the object ids returned by a query.
 *
 * @param {Object} state
 * @param {string} queryId
 * @return {Array<string|number>} List of queried ids.
 */
export function getQueriedObjectIds( state, queryId ) {
	return state.queries[ queryId ] || UNKNOWN_QUERIED_OBJECTS;
}

/**
 * Checks if this group could not be found.
 *
 * @param {Object} state The store state.
 * @param {string} id    The group id.
 * @return {boolean} True if not found.
 */
export function isGroupNotFound( state, id ) {
	return state.groupsNotFound.includes( id );
}

/**
 * Gets the data for the group.
 *
 * @param {Object} state
 * @param {string} id
 * @return {Object|undefined} Group data.
 */
export function getGroup( state, id ) {
	return state.byId[ id ];
}

/**
 * Get a group's attribute value.
 *
 * @param {Object} state
 * @param {string} id
 * @param {string} attribute
 * @return {*} Attribute value.
 */
export const getGroupAttribute = createRegistrySelector(
	( select ) => ( state, id, attribute ) => {
		const group = select( 'ithemes-security/user-groups' ).getGroup( id );

		return group ? group[ attribute ] : undefined;
	}
);

/**
 * Checks if the given user group is being updated.
 *
 * @param {Object} state
 * @param {string} id
 * @return {boolean} True if updating.
 */
export function isUpdating( state, id ) {
	return state.updating.includes( id );
}

/**
 * Checks if the given user group is being deleted.
 *
 * @param {Object} state
 * @param {string} id
 * @return {boolean} True if deleting.
 */
export function isDeleting( state, id ) {
	return state.deleting.includes( id );
}

/**
 * Get all the settings for a group.
 *
 * @param {Object} state
 * @param {string} id
 * @return {Object|undefined} Group settings.
 */
export function getGroupSettings( state, id ) {
	return state.settings[ id ];
}

/**
 * Get a group's value for a setting.
 *
 * @param {Object} state
 * @param {string} id
 * @param {string} module
 * @param {string} setting
 * @return {boolean} The setting value.
 */
export const getGroupSetting = createRegistrySelector(
	( select ) => ( state, id, module, setting ) => {
		const settings = select(
			'ithemes-security/user-groups'
		).getGroupSettings( id );

		return get( settings, [ module, setting ] );
	}
);

/**
 * Is the application updating a group's settings.
 *
 * @param {Object} state
 * @param {string} id
 * @return {boolean} True if updating.
 */
export function isUpdatingSettings( state, id ) {
	return state.updatingSettings.includes( id );
}

/**
 * Is a bulk patch in progress.
 *
 * @param {Object}        state
 * @param {Array<string>} groupIds
 * @param {Object}        patch
 * @return {boolean} True if bulk patching.
 */
export function isBulkPatchingSettings( state, groupIds, patch ) {
	const id = groupIds.join( '_' );

	return state.bulkPatchingSettings[ id ] === patch;
}

/**
 * Gets the groups for each setting.
 *
 * @param {Object} state State object.
 * @return {{}} Object of modules -> setting -> array of group ids.
 */
export function getGroupsBySetting( state ) {
	const bySetting = {};

	for ( const groupId in state.settings ) {
		if ( ! state.settings.hasOwnProperty( groupId ) ) {
			continue;
		}

		for ( const module in state.settings[ groupId ] ) {
			if ( ! state.settings[ groupId ].hasOwnProperty( module ) ) {
				continue;
			}

			for ( const setting in state.settings[ groupId ][ module ] ) {
				if (
					! state.settings[ groupId ][ module ].hasOwnProperty(
						setting
					)
				) {
					continue;
				}

				if ( ! bySetting[ module ] ) {
					bySetting[ module ] = {};
				}

				if ( ! bySetting[ module ][ setting ] ) {
					bySetting[ module ][ setting ] = [];
				}

				if ( state.settings[ groupId ][ module ][ setting ] ) {
					bySetting[ module ][ setting ].push( groupId );
				}
			}
		}
	}

	return bySetting;
}

function _getPassReqGroups( modules ) {
	return Object.fromEntries(
		modules
			.filter( ( module ) => ! isEmpty( module.password_requirements ) )
			.flatMap( ( module ) =>
				Object.entries( module.password_requirements )
					.filter( ( [ , definition ] ) =>
						definition.hasOwnProperty( 'user-group' )
					)
					.map( ( [ requirement, definition ] ) => [
						`requirement_settings.${ requirement }.group`,
						{
							title: definition.title || module.title,
							description:
								definition.description || module.description,
						},
					] )
			)
	);
}

const _getSettingDefinitions = memize(
	(
		ajv,
		filters,
		{ skipConditions = false },
		modules,
		activeModules,
		allSettings
	) => {
		const includeModule = ( module ) =>
			! filters.module || filters.module === module.id;

		return modules.reduce( ( definitions, module ) => {
			if ( module.status.selected !== 'active' ) {
				return definitions;
			}

			if ( ! includeModule( module ) ) {
				return definitions;
			}

			if (
				module.id !== 'password-requirements' &&
				isEmpty( module.user_groups )
			) {
				return definitions;
			}

			const settings = pickBy(
				module.id === 'password-requirements'
					? _getPassReqGroups( modules )
					: module.user_groups,
				( definition ) => {
					if ( ! definition.conditional || skipConditions ) {
						return true;
					}

					if ( definition.conditional[ 'active-modules' ] ) {
						for ( const activeModule of definition.conditional[
							'active-modules'
						] ) {
							if ( ! activeModules.includes( activeModule ) ) {
								return false;
							}
						}
					}

					if ( definition.conditional.settings ) {
						const validate = ajv.compile(
							definition.conditional.settings
						);

						if ( ! validate( allSettings[ module.id ] ) ) {
							return false;
						}
					}

					return true;
				}
			);

			if ( isEmpty( settings ) ) {
				return definitions;
			}

			definitions.push( {
				id: module.id,
				title: module.title,
				description: module.description,
				settings,
			} );

			return definitions;
		}, [] );
	},
	{ maxSize: 1 }
);

const EMPTY_OBJECT = {};

export const getSettingDefinitions = createRegistrySelector(
	( select ) => (
		state,
		ajv,
		filters = EMPTY_OBJECT,
		options = EMPTY_OBJECT
	) =>
		_getSettingDefinitions(
			ajv,
			filters,
			options,
			select( MODULES_STORE_NAME ).getEditedModules(),
			select( MODULES_STORE_NAME ).getActiveModules(),
			select( MODULES_STORE_NAME ).__unstableGetAllEditedSettings()
		)
);
