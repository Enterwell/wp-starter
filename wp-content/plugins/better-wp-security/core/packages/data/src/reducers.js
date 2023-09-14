/**
 * External dependencies
 */
import { omit } from 'lodash';

/**
 * Internal dependencies
 */
import {
	RECEIVE_ACTOR_TYPES,
	RECEIVE_ACTORS,
	RECEIVE_INDEX,
	RECEIVE_USER,
	RECEIVE_SITE_INFO,
	RECEIVE_CURRENT_USER_ID,
	LOAD_INITIAL_FEATURE_FLAGS,
	RECEIVE_BATCH_MAX_ITEMS,
} from './actions';

const DEFAULT_STATE = {
	users: {
		currentId: 0,
		byId: {},
		saving: [],
		optimisticEdits: {},
	},
	index: null,
	actors: {
		types: [],
		byType: {},
	},
	siteInfo: null,
	featureFlags: [],
	batchMaxItems: 0,
};

export default function reducer( state = DEFAULT_STATE, action ) {
	switch ( action.type ) {
		case RECEIVE_INDEX:
			return {
				...state,
				index: action.index,
			};
		case 'START_SAVING_USER':
			return {
				...state,
				users: {
					...state.users,
					saving: [
						...state.users.saving,
						action.id,
					],
					optimisticEdits: action.optimistic ? {
						...state.users.optimisticEdits,
						[ action.id ]: action.data,
					} : state.users.optimisticEdits,
				},
			};
		case 'FINISH_SAVING_USER':
		case 'FAILED_SAVING_USER':
			return {
				...state,
				users: {
					...state.users,
					saving: state.users.saving.filter( ( id ) => id !== action.id ),
					optimisticEdits: omit( state.users.optimisticEdits, action.id ),
				},
			};
		case RECEIVE_USER:
			return {
				...state,
				users: {
					...state.users,
					byId: {
						...state.users.byId,
						[ action.user.id ]: action.user,
					},
				},
			};
		case RECEIVE_CURRENT_USER_ID:
			return {
				...state,
				users: {
					...state.users,
					currentId: action.userId,
				},
			};
		case RECEIVE_ACTOR_TYPES:
			return {
				...state,
				actors: {
					...state.actors,
					types: action.types,
				},
			};
		case RECEIVE_ACTORS:
			return {
				...state,
				actors: {
					...state.actors,
					byType: {
						...state.actors.byType,
						[ action.actorType ]: action.actors,
					},
				},
			};
		case RECEIVE_SITE_INFO:
			return {
				...state,
				siteInfo: action.siteInfo,
			};
		case LOAD_INITIAL_FEATURE_FLAGS:
			return {
				...state,
				featureFlags: action.flags,
			};
		case RECEIVE_BATCH_MAX_ITEMS:
			return {
				...state,
				batchMaxItems: action.maxItems,
			};
		default:
			return state;
	}
}
