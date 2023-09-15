/**
 * External dependencies
 */
import { get, isEmpty, partition, pull, cloneDeep, merge } from 'lodash';
import memize from 'memize';
import createSelector from 'rememo';

/**
 * WordPress dependencies
 */
import { createRegistrySelector } from '@wordpress/data';

/**
 * Gets the list of local group ids that haven't been persisted yet.
 *
 * @param {Object} state
 * @return {Array<string>} List of ids.
 */
export function getLocalGroupIds( state ) {
	return state.localGroupIds;
}

/**
 * Checks if a group id corresponds to a locally created group that hasn't been persisted yet.
 *
 * @param {Object} state
 * @param {string} id
 * @return {boolean} True if a local group.
 */
export function isLocalGroup( state, id ) {
	return state.localGroupIds.includes( id );
}

/**
 * Gets the type of a matchable.
 *
 * @param {Object} state
 * @param {string} id    The matchable id.
 * @return string The matchable type.
 */
export const getMatchableType = createRegistrySelector(
	( select ) => ( state, id ) => {
		if ( isLocalGroup( state, id ) ) {
			return 'user-group';
		}

		return select( 'ithemes-security/user-groups' ).getMatchableType( id );
	}
);

export const isSavingGroupOrSettings = createRegistrySelector(
	( select ) => ( state, id ) => {
		return (
			state.saving.includes( id ) ||
			select( 'ithemes-security/user-groups' ).isUpdating( id ) ||
			select( 'ithemes-security/user-groups' ).isUpdatingSettings( id )
		);
	}
);

/**
 * Get the full edited group object.
 *
 * @param {Object} state
 * @param {string} id
 * @return {Object|undefined} The group's edits.
 */
export function getEditedGroup( state, id ) {
	return state.edits[ id ];
}

/**
 * Get the edited attribute for a group.
 *
 * @param {Object} state
 * @param {string} id
 * @param {string} attribute
 * @return {*} The attribute value.
 */
export const getEditedGroupAttribute = createRegistrySelector(
	( select ) => ( state, id, attribute ) => {
		const edited = get( state, [ 'edits', id, attribute ] );

		if ( typeof edited !== 'undefined' ) {
			return edited;
		}

		if ( id === 'new' ) {
			return undefined;
		}

		if ( isLocalGroup( state, id ) ) {
			return undefined;
		}

		return select( 'ithemes-security/user-groups' ).getGroupAttribute(
			id,
			attribute
		);
	}
);

/**
 * Gets the edited label for a matchable.
 *
 * @param {Object} state
 * @param {string} id
 * @return {string} The edited label.
 */
export const getEditedMatchableLabel = createRegistrySelector(
	( select ) => ( state, id ) => {
		const type = select(
			'ithemes-security/user-groups-editor'
		).getMatchableType( id );

		if ( type !== 'user-group' ) {
			return select( 'ithemes-security/user-groups' ).getMatchableLabel(
				id
			);
		}

		return select(
			'ithemes-security/user-groups-editor'
		).getEditedGroupAttribute( id, 'label' );
	}
);

/**
 * Checks if there are changes to be saved for a group.
 *
 * @param {Object} state
 * @param {string} id
 * @return {boolean} True if is edited.
 */
export function hasEdits( state, id ) {
	return state.edits[ id ] && ! isEmpty( state.edits[ id ] );
}

/**
 * Gets the list of dirty groups.
 *
 * @return {Array<string>} The list of dirty group ids.
 */
export const getDirtyGroups = createSelector(
	( state ) =>
		Object.keys( state.edits ).filter(
			( id ) => ! isEmpty( state.edits[ id ] )
		),
	( state ) => state.edits
);

/**
 * Are there any unsaved changes for a group's settings.
 *
 * @param {Object} state
 * @param {string} id
 * @return {boolean} True if edited.
 */
export function settingHasEdits( state, id ) {
	return !! state.settingEdits[ id ];
}

/**
 * Get the full edited group settings.
 *
 * @param {Object} state
 * @param {string} id
 * @return {Object} The list of edited settings.
 */
export function getGroupSettingsEdits( state, id ) {
	return state.settingEdits[ id ];
}

const _getEditedGroupSettings = memize(
	( settings, edits ) => merge( {}, settings, edits ),
	{ maxSize: 1 }
);

export const getEditedGroupSettings = createRegistrySelector(
	( select ) => ( state, id ) => {
		const isLocal = isLocalGroup( state, id );
		const settings = isLocal
			? {}
			: select( 'ithemes-security/user-groups' ).getGroupSettings( id );
		const edits = getGroupSettingsEdits( state, id );

		return _getEditedGroupSettings( settings, edits );
	}
);

/**
 * Get a group's edited value for a setting.
 *
 * @param {Object} state
 * @param {string} id
 * @param {string} module
 * @param {string} setting
 * @return {boolean} The edited setting value.
 */
export const getEditedGroupSetting = createRegistrySelector(
	( select ) => ( state, id, module, setting ) => {
		const value = get( state, [ 'settingEdits', id, module, setting ] );

		if ( value !== undefined ) {
			return value;
		}

		if ( isLocalGroup( state, id ) ) {
			return false;
		}

		return select( 'ithemes-security/user-groups' ).getGroupSetting(
			id,
			module,
			setting
		);
	}
);

/**
 * Gets the groups for each setting.
 *
 * @param {Object} state State object.
 * @return {{}} Object of modules -> setting -> array of group ids.
 */
export const getEditedGroupsBySetting = createRegistrySelector(
	( select ) => ( state ) => {
		const bySetting = select(
			'ithemes-security/user-groups'
		).getGroupsBySetting();

		if ( isEmpty( state.settingEdits ) || isEmpty( bySetting ) ) {
			return bySetting;
		}

		const clone = cloneDeep( bySetting );

		for ( const [ groupId, moduleSettings ] of Object.entries(
			state.settingEdits
		) ) {
			for ( const [ module, settings ] of Object.entries(
				moduleSettings
			) ) {
				for ( const [ setting, value ] of Object.entries( settings ) ) {
					if ( ! clone[ module ]?.[ setting ] ) {
						continue;
					}

					if ( value ) {
						clone[ module ][ setting ].push( groupId );
					} else {
						pull( clone[ module ][ setting ], groupId );
					}
				}
			}
		}

		return clone;
	}
);

/**
 * Gets the list of groups with dirty settings.
 *
 * @return {Array<string>} The list of dirty group ids.
 */
export const getDirtyGroupSettings = createSelector(
	( state ) =>
		Object.keys( state.settingEdits ).filter(
			( id ) => ! isEmpty( state.settingEdits[ id ] )
		),
	( state ) => state.settingEdits
);

/**
 * Checks if a group has edits or edited settings.
 *
 * @param {Object} state   The state object.
 * @param {string} groupId The group id to check.
 * @return {boolean} True if dirty.
 */
export function isDirty( state, groupId ) {
	return hasEdits( state, groupId ) || settingHasEdits( state, groupId );
}

/**
 * Check if there have been bulk setting edits.
 *
 * @param {Object} state
 * @return {boolean} True if the bulk settings are edited.
 */
export function hasBulkSettingEdits( state ) {
	return ! isEmpty( state.bulkSettingEdits );
}

/**
 * Get all the bulk setting edits.
 *
 * @param {Object} state
 * @return {{}} The bulk edits.
 */
export function getBulkSettingEdits( state ) {
	return state.bulkSettingEdits;
}

/**
 * Get the bulk edited setting value.
 *
 * @param {Object} state
 * @param {string} module
 * @param {string} setting
 * @return {boolean|undefined} The edited setting value.
 */
export function getBulkSettingEdit( state, module, setting ) {
	return get( state, [ 'bulkSettingEdits', module, setting ] );
}

/**
 * Get the value for a bulk edited setting.
 *
 * @param {Object}        state
 * @param {Array<Object>} groupIds
 * @param {string}        module
 * @param {string}        setting
 * @return {null|boolean} The setting value.
 */
export const getBulkSettingValue = createRegistrySelector(
	( select ) => ( state, groupIds, module, setting ) => {
		const edit = getBulkSettingEdit( state, module, setting );

		if ( edit !== undefined ) {
			return edit;
		}

		const getValue = ( groupId ) =>
			select( 'ithemes-security/user-groups' ).getGroupSetting(
				groupId,
				module,
				setting
			);

		const firstVal = getValue( groupIds[ 0 ] );
		const allSame = groupIds.every(
			( groupId ) => getValue( groupId ) === firstVal
		);

		if ( allSame ) {
			return firstVal;
		}

		return null;
	}
);

/**
 * Are bulk edits being saved.
 *
 * @param {Object}        state    The state object.
 * @param {Array<string>} groupIds The list of group ids.
 * @return {boolean} True if saving.
 */
export const isSavingBulkEdits = createRegistrySelector(
	( select ) => ( state, groupIds ) => {
		const edits = getBulkSettingEdits( state );

		return select( 'ithemes-security/user-groups' ).isBulkPatchingSettings(
			groupIds,
			edits
		);
	}
);

/**
 * Get the list of available groups.
 *
 * @return {Array<Object>} The list of groups.
 */
export const getAvailableGroups = createRegistrySelector( ( select ) => () => {
	return select( 'ithemes-security/user-groups' ).getGroups( 'available' );
} );

const _toNavIds = memize(
	( matchables, resolving, localGroups, toDelete ) => {
		if ( resolving && ! matchables.length ) {
			return null;
		}

		const [ userGroups, generic ] = partition(
			matchables,
			( matchable ) => matchable.type === 'user-group'
		);

		return userGroups
			.map( ( matchable ) => matchable.id )
			.concat( localGroups )
			.concat( generic.map( ( matchable ) => matchable.id ) )
			.filter( ( id ) => ! toDelete.includes( id ) );
	},
	{ maxSize: 1 }
);

/**
 * Gets the list of matchable ids for use in navigation.
 *
 * @return {Array<string>} List of nav ids.
 */
export const getMatchableNavIds = createRegistrySelector( ( select ) => () => {
	const matchables = select( 'ithemes-security/user-groups' ).getMatchables();
	const resolving = select( 'core/data' ).isResolving(
		'ithemes-security/user-groups',
		'getMatchables'
	);
	const localGroups = select(
		'ithemes-security/user-groups-editor'
	).getLocalGroupIds();
	const toDelete = select(
		'ithemes-security/user-groups-editor'
	).getGroupsMarkedForDeletion();

	return _toNavIds( matchables, resolving, localGroups, toDelete );
} );

/**
 * Gets the list of group ids that are marked to be deleted.
 *
 * @param {Object} state The state object.
 * @return {Array<string>} List of group ids.
 */
export function getGroupsMarkedForDeletion( state ) {
	return state.markedForDelete;
}

/**
 * Gets the last error associated with a group.
 *
 * @param {Object} state   The state object.
 * @param {string} groupId The group id to check.
 * @return {Object|undefined} The error, if any.
 */
export function getError( state, groupId ) {
	return state.errors[ groupId ];
}

/**
 * Gets the bulk save error list.
 *
 * @param {Object} state The state object.
 * @return {Array<string>} List of error messages.
 */
export function getBulkErrorsList( state ) {
	return state.bulkErrors;
}
