import { RECEIVE_PRIMARY_DASHBOARD } from './actions';

const DEFAULT_STATE = {
	primaryDashboard: undefined,
};

export default function user( state = DEFAULT_STATE, action ) {
	switch ( action.type ) {
		case RECEIVE_PRIMARY_DASHBOARD:
			return {
				...state,
				primaryDashboard: action.dashboardId,
			};
		default:
			return state;
	}
}
