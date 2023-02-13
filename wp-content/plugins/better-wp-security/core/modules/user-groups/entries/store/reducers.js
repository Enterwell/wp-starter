/**
 * External dependencies
 */
import { omit, keyBy, map } from 'lodash';

/**
 * Internal dependencies
 */
import {
	FAILED_CREATE_GROUP,
	FAILED_DELETE_GROUP,
	FAILED_UPDATE_GROUP,
	FINISH_CREATE_GROUP,
	FINISH_DELETE_GROUP,
	FINISH_UPDATE_GROUP,
	RECEIVE_GROUP,
	RECEIVE_GROUP_SETTINGS,
	RECEIVE_QUERY,
	START_CREATE_GROUP,
	START_DELETE_GROUP,
	START_UPDATE_GROUP,
	START_UPDATE_GROUP_SETTINGS,
	FINISH_UPDATE_GROUP_SETTINGS,
	FAILED_UPDATE_GROUP_SETTINGS,
	APPEND_TO_QUERY,
	START_PATCH_BULK_GROUP_SETTINGS,
	FINISH_PATCH_BULK_GROUP_SETTINGS,
	FAILED_PATCH_BULK_GROUP_SETTINGS, RECEIVE_MATCHABLES,
} from './actions';

const DEFAULT_STATE = {
	matchablesById: {},
	matchableIds: [],
	byId: {},
	queries: {},
	creating: [],
	updating: [],
	deleting: [],
	settings: {},
	updatingSettings: [],
	bulkPatchingSettings: {},
};

export default function userGroups( state = DEFAULT_STATE, action ) {
	switch ( action.type ) {
		case RECEIVE_MATCHABLES:
			return {
				...state,
				matchableIds: map( action.matchables, 'id' ),
				matchablesById: keyBy( action.matchables, 'id' ),
			};
		case RECEIVE_QUERY:
			return {
				...state,
				byId: {
					...state.byId,
					...keyBy( action.items, 'id' ),
				},
				queries: {
					...state.queries,
					[ action.queryId ]: map( action.items, 'id' ),
				},
			};
		case APPEND_TO_QUERY:
			return {
				...state,
				byId: {
					...state.byId,
					[ action.item.id ]: action.item,
				},
				queries: {
					...state.queries,
					[ action.queryId ]: [
						...( state.queries[ action.queryId ] || [] ),
						action.item.id,
					],
				},
			};
		case RECEIVE_GROUP:
			return {
				...state,
				byId: {
					...state.byId,
					[ action.group.id ]: action.group,
				},
				matchablesById: state.matchablesById[ action.group.id ] ? {
					...state.matchablesById,
					[ action.group.id ]: {
						...state.matchablesById[ action.group.id ],
						label: action.group.label,
					},
				} : state.matchablesById,
			};
		case START_CREATE_GROUP:
			return {
				...state,
				creating: [
					...state.creating,
					action.group,
				],
			};
		case FINISH_CREATE_GROUP:
			return {
				...state,
				creating: state.creating.filter( ( group ) => group !== action.group ),
				matchablesById: {
					...state.matchablesById,
					[ action.response.id ]: {
						id: action.response.id,
						label: action.response.label,
						type: 'user-group',
					},
				},
				matchableIds: [
					...state.matchableIds,
					[ action.response.id ],
				],
			};
		case FAILED_CREATE_GROUP:
			return {
				...state,
				creating: state.creating.filter( ( group ) => group !== action.group ),
			};
		case START_UPDATE_GROUP:
			return {
				...state,
				updating: [
					...state.updating,
					action.id,
				],
			};
		case FINISH_UPDATE_GROUP:
		case FAILED_UPDATE_GROUP:
			return {
				...state,
				updating: state.updating.filter( ( id ) => id !== action.id ),
			};
		case START_DELETE_GROUP:
			return {
				...state,
				deleting: [
					...state.deleting,
					action.id,
				],
			};
		case FINISH_DELETE_GROUP:
			return {
				...state,
				deleting: state.deleting.filter( ( id ) => id !== action.id ),
				byId: omit( state.byId, [ action.id ] ),
				matchablesById: omit( state.matchablesById, [ action.id ] ),
				matchableIds: state.matchableIds.filter( ( id ) => id !== action.id ),
				settings: omit( state.settings, [ action.id ] ),
			};
		case FAILED_DELETE_GROUP:
			return {
				...state,
				deleting: state.deleting.filter( ( id ) => id !== action.id ),
			};
		case RECEIVE_GROUP_SETTINGS:
			return {
				...state,
				settings: {
					...state.settings,
					[ action.id ]: action.settings,
				},
			};
		case START_UPDATE_GROUP_SETTINGS:
			return {
				...state,
				updatingSettings: [
					...state.updatingSettings,
					action.id,
				],
			};
		case FINISH_UPDATE_GROUP_SETTINGS:
		case FAILED_UPDATE_GROUP_SETTINGS:
			return {
				...state,
				updatingSettings: state.updatingSettings.filter( ( id ) => id !== action.id ),
			};
		case START_PATCH_BULK_GROUP_SETTINGS:
			return {
				...state,
				bulkPatchingSettings: {
					...state.bulkPatchingSettings,
					[ action.groupIds.join( '_' ) ]: action.patch,
				},
			};
		case FINISH_PATCH_BULK_GROUP_SETTINGS:
		case FAILED_PATCH_BULK_GROUP_SETTINGS:
			return {
				...state,
				bulkPatchingSettings: omit( state.bulkPatchingSettings, [ action.groupIds.join( '_' ) ] ),
			};
		default:
			return state;
	}
}
