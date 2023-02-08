/**
 * Internal dependencies
 */
import { RECEIVE_INDEX, RECEIVE_USER } from './actions';

const DEFAULT_STATE = {
	users: {
		byId: {},
	},
	index: null,
};

export default function reducer( state = DEFAULT_STATE, action ) {
	switch ( action.type ) {
		case RECEIVE_INDEX:
			return {
				...state,
				index: action.index,
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
		default:
			return state;
	}
}
