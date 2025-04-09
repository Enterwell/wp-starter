/**
 * External dependencies
 */
import { fromPairs, get, map, omit, omitBy } from 'lodash';
import { parse } from 'li';

/**
 * WordPress dependencies
 */
import { combineReducers } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { getSelf } from '@ithemes/security-utils';
import {
	START_QUERY,
	FINISH_QUERY,
	FAILED_QUERY,
	RECEIVE_QUERY,
	RECEIVE_ITEM,
} from './actions';

const DEFAULT_QUERY_STATE = {
	bySelf: {},
	selfById: {},
	queries: {},
	queryParams: {},
	querying: [],
};

function query( state = DEFAULT_QUERY_STATE, action ) {
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
		case RECEIVE_ITEM:
			return {
				...state,
				bySelf: {
					...state.bySelf,
					[ getSelf( action.item ) ]: {
						context: 'edit',
						item: action.item,
					},
				},
				selfById: {
					...state.selfById,
					[ action.item.id ]: getSelf( action.item ),
				},
			};
		case 'FINISH_DELETING':
			return {
				...state,
				bySelf: omit( state.bySelf, action.self ),
				selfById: omitBy( state.selfById, ( self ) => self === action.self ),
			};
		default:
			return state;
	}
}

const DEFAULT_EDITS_STATE = {
	bySelf: {},
};

function edits( state = DEFAULT_EDITS_STATE, action ) {
	switch ( action.type ) {
		case 'EDIT_ITEM':
			return {
				...state,
				bySelf: {
					...state.bySelf,
					[ action.self ]: {
						...( state.bySelf[ action.self ] || {} ),
						...action.edit,
					},
				},
			};
		case 'RESET_EDITS':
			return {
				...state,
				bySelf: omit( state.bySelf, action.self ),
			};
		case 'RESET_ALL_EDITS':
			return {
				...state,
				bySelf: {},
			};
		case 'FINISH_DELETING':
			return {
				...state,
				bySelf: omit( state.bySelf, action.self ),
			};
		default:
			return state;
	}
}

const DEFAULT_WRITE_STATE = {
	selves: [],
	errors: {},
};

function saving( state = DEFAULT_WRITE_STATE, action ) {
	switch ( action.type ) {
		case 'START_SAVING':
			return {
				...state,
				selves: [ ...state.selves, action.self ],
				errors: omit( state.errors, action.self ),
			};
		case 'FINISH_SAVING':
			return {
				...state,
				selves: state.selves.filter( ( self ) => action.self !== self ),
			};
		case 'FAILED_SAVING':
			return {
				...state,
				selves: state.selves.filter( ( self ) => action.self !== self ),
				errors: { ...state.errors, [ action.self ]: action.error },
			};
		default:
			return state;
	}
}

function deleting( state = DEFAULT_WRITE_STATE, action ) {
	switch ( action.type ) {
		case 'START_DELETING':
			return {
				...state,
				selves: [ ...state.selves, action.self ],
				errors: omit( state.errors, action.self ),
			};
		case 'FINISH_DELETING':
			return {
				...state,
				selves: state.selves.filter( ( self ) => action.self !== self ),
			};
		case 'FAILED_DELETING':
			return {
				...state,
				selves: state.selves.filter( ( self ) => action.self !== self ),
				errors: { ...state.errors, [ action.self ]: action.error },
			};
		default:
			return state;
	}
}

export default combineReducers( { query, edits, saving, deleting } );
