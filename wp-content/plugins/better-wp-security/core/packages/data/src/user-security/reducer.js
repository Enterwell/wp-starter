/**
 * External dependencies
 */
import { parse } from 'li';
import { fromPairs, get, map, omit } from 'lodash';

/**
 * Internal dependencies
 */
import { getSelf } from '@ithemes/security-utils';
import {
	CLOSE_QUICK_EDIT,
	CONFIRM_QUICK_EDIT,
	FAILED_ACTION,
	FAILED_QUERY,
	FINISH_ACTION,
	FINISH_QUERY,
	OPEN_QUICK_EDIT,
	RECEIVE_QUERY,
	RECEIVE_USER,
	REMOVE_SELECTED_USERS,
	START_ACTION,
	START_QUERY,
	TOGGLE_SELECT_ALL,
	TOGGLE_SELECTED_USER,
	UPDATE_SELECTION_TYPE,
} from './actions';
import { getQueryResults } from './selectors';

const DEFAULT_STATE = {
	bySelf: {},
	selfById: {},
	userSelection: 'window',
	queries: {},
	queryParams: {},
	querying: [],
	actions: [],
	actionErrors: {},
	selectedUsers: [],
	quickEditState: false,
};

export default function users( state = DEFAULT_STATE, action ) {
	switch ( action.type ) {
		case RECEIVE_QUERY:
			return {
				...state,
				queries: {
					...state.queries,
					[ action.queryId ]: {
						selves:
							action.mode === 'replace'
								? map( action.items, getSelf )
								: [
									...get(
										state,
										[
											'queries',
											action.queryId,
											'selves',
										],
										[]
									),
									...map( action.items, getSelf ),
								],
						headers: fromPairs(
							Array.from( action.response.headers.entries() )
						),
						links: parse( action.response.headers.get( 'link' ), {
							extended: true,
						} ),
					},
				},
				bySelf: {
					...state.bySelf,
					...fromPairs(
						action.items
							.filter( ( item ) => {
								const self = getSelf( item );

								if ( ! state.bySelf[ self ] ) {
									return true;
								}

								return (
									state.bySelf[ self ].context === 'embed' ||
									state.bySelf[ self ].context ===
									action.context
								);
							} )
							.map( ( item ) => [
								getSelf( item ),
								{
									context: action.context,
									item,
								},
							] )
					),
				},
				selfById: {
					...state.selfById,
					...fromPairs( action.items.map( ( item ) => [
						item.id,
						getSelf( item ),
					] ) ),
				},
				selectedUsers: action.queryId === 'main' ? [] : state.selectedUsers,
			};
		case START_QUERY:
			return {
				...state,
				querying: [ ...state.querying, action.queryId ],
				queryParams: {
					...state.queryParams,
					[ action.queryId ]: action.queryParams || state.queryParams[ action.queryId ],
				},
			};
		case FINISH_QUERY:
		case FAILED_QUERY:
			return {
				...state,
				querying: state.querying.filter(
					( queryId ) => queryId !== action.queryId
				),
			};
		case RECEIVE_USER:
			return {
				...state,
				bySelf: {
					...state.bySelf,
					[ getSelf( action.user ) ]: {
						context: 'edit',
						item: action.user,
					},
				},
				selfById: {
					...state.selfById,
					[ action.user.id ]: getSelf( action.user ),
				},
			};
		case UPDATE_SELECTION_TYPE:
			return {
				...state,
				userSelection: action.userSelectionType,
			};
		case TOGGLE_SELECT_ALL:
			const queriedUsers = getQueryResults( state, 'main' );
			if ( state.selectedUsers.length === queriedUsers.length ) {
				return {
					...state,
					selectedUsers: [],
				};
			}

			return {
				...state,
				selectedUsers: queriedUsers
					.map( ( user ) => user.id ),
			};
		case TOGGLE_SELECTED_USER:
			if ( state.selectedUsers.includes( action.user.id ) ) {
				return {
					...state,
					selectedUsers:
						state.selectedUsers.filter( ( id ) => id !== action.user.id ),
					userSelection: 'all',
				};
			}

			return {
				...state,
				selectedUsers: [
					...state.selectedUsers,
					action.user.id,
				],
				userSelection: 'window',
			};
		case REMOVE_SELECTED_USERS:
			return {
				...state,
				selectedUsers: [],
				userSelection: 'none',
			};
		case OPEN_QUICK_EDIT:
			return {
				...state,
				quickEditState: true,
			};
		case CLOSE_QUICK_EDIT:
			return {
				...state,
				quickEditState: false,
			};
		case CONFIRM_QUICK_EDIT:
			return {
				...state,
				quickEditState: 'confirm',
			};
		case START_ACTION:
			return {
				...state,
				actions: [ ...state.actions, action.id ],
				actionErrors: omit( state.actionErrors, action.id ),
			};
		case FINISH_ACTION:
			return {
				...state,
				actions: state.actions.filter(
					( id ) => id !== action.id
				),
			};
		case FAILED_ACTION:
			return {
				...state,
				actions: state.actions.filter(
					( id ) => id !== action.id
				),
				actionErrors: {
					...state.actionErrors,
					[ action.id ]: action.error,
				},
			};
		default:
			return state;
	}
}
