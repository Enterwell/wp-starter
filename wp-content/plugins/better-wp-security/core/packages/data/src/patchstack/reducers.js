/**
 * External dependencies
 */
import { fromPairs, get, map, omit } from 'lodash';
import { parse } from 'li';

/**
 * Internal dependencies
 */
import {
	RECEIVE_QUERY,
	START_QUERY,
	FINISH_QUERY,
	FAILED_QUERY,
} from './actions';

const DEFAULT_STATE = {
	byId: {},
	queries: {},
	queryParams: {},
	querying: [],
	actions: [],
};

export default function patchstackVulnerabilities( state = DEFAULT_STATE, action ) {
	switch ( action.type ) {
		case RECEIVE_QUERY:
			return {
				...state,
				queries: {
					...state.queries,
					[ action.queryId ]: {
						ids:
							action.mode === 'replace'
								? map( action.items, 'id' )
								: [
									...get(
										state,
										[
											'queries',
											action.queryId,
											'ids',
										],
										[]
									),
									...map( action.items, 'id' ),
								],
						headers: fromPairs(
							Array.from( action.response.headers.entries() )
						),
						links: parse( action.response.headers.get( 'link' ), {
							extended: true,
						} ),
					},
				},
				byId: {
					...state.byId,
					...fromPairs(
						action.items
							.filter( ( item ) => {
								const id = item.id;

								if ( ! state.byId[ id ] ) {
									return true;
								}

								return (
									state.byId[ id ].context === 'embed' ||
									state.byId[ id ].context ===
									action.context
								);
							} )
							.map( ( item ) => [
								item.id,
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
				errors: omit( state.errors, [ action.queryId ] ),
			};
		case FINISH_QUERY:
			return {
				...state,
				querying: state.querying.filter(
					( queryId ) => queryId !== action.queryId
				),
			};
		case FAILED_QUERY:
			return {
				...state,
				querying: state.querying.filter(
					( queryId ) => queryId !== action.queryId
				),
				errors: {
					...state.errors,
					[ action.queryId ]: action.error,
				},
			};
		default:
			return state;
	}
}
