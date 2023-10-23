/**
 * External dependencies
 */
import { get, map, fromPairs } from 'lodash';
import { parse } from 'li';

/**
 * Internal dependencies
 */
import { getSelf } from '@ithemes/security-utils';
import {
	RECEIVE_QUERY,
	RECEIVE_VULNERABILITY,
	START_QUERY,
	FINISH_QUERY,
	FAILED_QUERY,
	START_ACTION,
	FINISH_ACTION,
	FAILED_ACTION,
} from './actions';

const DEFAULT_STATE = {
	bySelf: {},
	selfById: {},
	queries: {},
	queryParams: {},
	querying: [],
	actions: [],
};

export default function vulnerabilities( state = DEFAULT_STATE, action ) {
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
		case RECEIVE_VULNERABILITY:
			return {
				...state,
				bySelf: {
					...state.bySelf,
					[ getSelf( action.vulnerability ) ]: {
						context: 'edit',
						item: action.vulnerability,
					},
				},
				selfById: {
					...state.selfById,
					[ action.vulnerability.id ]: getSelf( action.vulnerability ),
				},
			};
		case START_ACTION:
			return {
				...state,
				actions: [ ...state.actions, `${ action.rel }:${ action.self }` ],
			};
		case FINISH_ACTION:
		case FAILED_ACTION:
			return {
				...state,
				actions: state.actions.filter(
					( id ) => id !== `${ action.rel }:${ action.self }`
				),
			};
		default:
			return state;
	}
}
