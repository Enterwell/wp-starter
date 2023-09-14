/**
 * External dependencies
 */
import { get, map, fromPairs, omit } from 'lodash';
import { parse } from 'li';

/**
 * Internal dependencies
 */
import { getSelf } from '@ithemes/security-utils';
import {
	RECEIVE_QUERY,
	RECEIVE_BAN,
	START_QUERY,
	FINISH_QUERY,
	FAILED_QUERY,
	START_CREATE_BAN,
	FINISH_CREATE_BAN,
	FAILED_CREATE_BAN,
	START_UPDATE_BAN,
	FINISH_UPDATE_BAN,
	FAILED_UPDATE_BAN,
	START_DELETE_BAN,
	FINISH_DELETE_BAN,
	FAILED_DELETE_BAN,
} from './actions';

const DEFAULT_STATE = {
	bySelf: {},
	queries: {},
	queryParams: {},
	querying: [],
	creating: [],
	updating: [],
	deleting: [],
};

export default function bans( state = DEFAULT_STATE, action ) {
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
						} ).map( ( link ) => ( {
							...link,
							rel: link.rel[ 0 ],
						} ) ),
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
		case RECEIVE_BAN:
			return {
				...state,
				bySelf: {
					...state.bySelf,
					[ getSelf( action.ban ) ]: {
						context: 'edit',
						item: action.ban,
					},
				},
			};
		case START_CREATE_BAN:
			return {
				...state,
				creating: [ ...state.creating, action.ban ],
			};
		case FINISH_CREATE_BAN:
		case FAILED_CREATE_BAN:
			return {
				...state,
				creating: state.creating.filter(
					( ban ) => ban !== action.ban
				),
			};
		case START_UPDATE_BAN:
			return {
				...state,
				updating: [ ...state.updating, action.self ],
			};
		case FINISH_UPDATE_BAN:
		case FAILED_UPDATE_BAN:
			return {
				...state,
				updating: state.updating.filter(
					( self ) => self !== action.self
				),
			};
		case START_DELETE_BAN:
			return {
				...state,
				deleting: [ ...state.deleting, action.self ],
				bySelf: omit( state.bySelf, [ action.self ] ),
			};
		case FINISH_DELETE_BAN:
		case FAILED_DELETE_BAN:
			return {
				...state,
				deleting: state.deleting.filter(
					( self ) => self !== action.self
				),
			};
		default:
			return state;
	}
}
