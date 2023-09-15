/**
 * External dependencies
 */
import { keyBy, map, without, omit } from 'lodash';

/**
 * Internal dependencies
 */
import {
	RECEIVE_TOOLS,
	START_TOOL,
	FINISH_TOOL,
	START_TOGGLE_TOOL,
	FAILED_TOGGLE_TOOL,
	FINISH_TOGGLE_TOOL,
} from './actions';

const DEFAULT_STATE = {
	bySlug: {},
	slugs: [],
	running: [],
	lastResult: {},
	updating: [],
	lastError: {},
};

export default function( state = DEFAULT_STATE, action ) {
	switch ( action.type ) {
		case RECEIVE_TOOLS:
			return {
				...state,
				bySlug: keyBy( action.tools, 'slug' ),
				slugs: map( action.tools, 'slug' ),
			};
		case START_TOOL:
			return {
				...state,
				running: [ ...state.running, action.tool ],
			};
		case FINISH_TOOL:
			return {
				...state,
				running: without( state.running, action.tool ),
				lastResult: {
					...state.lastResult,
					[ action.tool ]: action.result,
				},
			};
		case START_TOGGLE_TOOL:
			return {
				...state,
				updating: [ ...state.updating, action.tool ],
			};
		case FAILED_TOGGLE_TOOL:
			return {
				...state,
				updating: without( state.updating, action.tool ),
				lastError: {
					...state.lastError,
					[ action.tool ]: action.error,
				},
			};
		case FINISH_TOGGLE_TOOL:
			return {
				...state,
				updating: without( state.updating, action.tool ),
				lastError: omit( state.lastError, action.tool ),
				bySlug: {
					...state.bySlug,
					[ action.tool ]: action.data,
				},
			};
		default:
			return state;
	}
}
