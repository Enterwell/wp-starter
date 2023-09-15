/**
 * Internal dependencies
 */
import { RECEIVE_HELP } from './actions';

const INITIAL_STATE = {
	byTopic: {},
};

export default function( state = INITIAL_STATE, action ) {
	switch ( action.type ) {
		case RECEIVE_HELP:
			return {
				...state,
				byTopic: {
					...state.byTopic,
					[ action.topic ]: action.help,
				},
			};
		default:
			return state;
	}
}
