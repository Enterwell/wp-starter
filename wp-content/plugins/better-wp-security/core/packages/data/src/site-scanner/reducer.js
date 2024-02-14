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
	RECEIVE_SCAN,
	START_QUERY,
	FINISH_QUERY,
	FAILED_QUERY,
	START_SCAN,
	FINISH_SCAN,
	FAILED_SCAN,
} from './actions';

const DEFAULT_STATE = {
	bySelf: {},
	selfById: {},
	queries: {},
	queryParams: {},
	querying: [],
	scanning: null,
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
		case RECEIVE_SCAN:
			return {
				...state,
				bySelf: {
					...state.bySelf,
					[ getSelf( action.scan ) ]: {
						context: 'edit',
						item: action.scan,
					},
				},
			};
		case START_SCAN:
			return {
				...state,
				scanning: action.siteId,
			};
		case FINISH_SCAN:
		case FAILED_SCAN:
			return {
				...state,
				scanning: null,
			};
		default:
			return state;
	}
}
