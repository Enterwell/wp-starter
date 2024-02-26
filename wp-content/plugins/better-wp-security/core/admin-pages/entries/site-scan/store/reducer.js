import {
	REGISTER_COMPONENT,
	REGISTER_COMPONENT_GROUP,
	START_COMPONENT_SCAN,
	FAILED_COMPONENT_SCAN,
	FINISH_COMPONENT_SCAN,
	START_SCAN,
	FINISH_SCAN,
	FAILED_GROUP_SCAN,
	START_ACTION,
	FINISH_ACTION,
	RECEIVE_ISSUE,
	FAILED_ACTION,
} from './actions';
import { STATUS_BUSY, STATUS_FAILED, STATUS_DONE } from './constant';

const DEFAULT_STATE = {
	components: {},
	componentGroups: {},
	componentStatus: {},
	componentErrors: {},
	groupErrors: {},
	issues: [],
	isRunning: false,
	actions: [],
};

export default function siteScan( state = DEFAULT_STATE, action ) {
	switch ( action.type ) {
		case REGISTER_COMPONENT:
			return {
				...state,
				components: {
					...state.components,
					[ action.args.slug ]: action.args,
				},
			};
		case REGISTER_COMPONENT_GROUP:
			return {
				...state,
				componentGroups: {
					...state.componentGroups,
					[ action.args.slug ]: action.args,
				},
			};
		case START_COMPONENT_SCAN:
			return {
				...state,
				componentStatus: {
					...state.componentStatus,
					[ action.component ]: STATUS_BUSY,
				},
			};
		case FAILED_COMPONENT_SCAN:
			return {
				...state,
				componentStatus: {
					...state.componentStatus,
					[ action.component ]: STATUS_FAILED,
				},
				componentErrors: {
					...state.componentErrors,
					[ action.component ]: action.error,
				},
			};
		case FINISH_COMPONENT_SCAN:
			return {
				...state,
				componentStatus: {
					...state.componentStatus,
					[ action.component ]: STATUS_DONE,
				},
				issues: [
					...state.issues,
					...action.issues,
				],
			};
		case FAILED_GROUP_SCAN:
			return {
				...state,
				groupErrors: {
					...state.groupErrors,
					[ action.group ]: action.error,
				},
				componentStatus: {
					...state.componentStatus,
					...( state
						.componentGroups[ action.group ]
						?.components.reduce( ( acc, component ) => {
							acc[ component ] = STATUS_FAILED;

							return acc;
						}, {} ) || {} ),
				},
			};
		case START_SCAN:
			return {
				...state,
				componentStatus: {},
				componentErrors: {},
				groupErrors: {},
				issues: [],
				isRunning: true,
			};
		case FINISH_SCAN:
			return {
				...state,
				isRunning: false,
			};
		case START_ACTION:
			return {
				...state,
				actions: [
					...state.actions,
					`${ action.rel }:${ action.issue.component }:${ action.issue.id }`,
				],
			};
		case FINISH_ACTION:
		case FAILED_ACTION:
			return {
				...state,
				actions: state.actions.filter( ( issueAction ) => issueAction !== `${ action.rel }:${ action.issue.component }:${ action.issue.id }` ),
			};
		case RECEIVE_ISSUE:
			const index = state.issues.findIndex( ( issue ) => issue.id === action.issue.id && issue.component === action.issue.component );

			if ( index === -1 ) {
				return {
					...state,
					issues: [
						...state.issues,
						action.issue,
					],
				};
			}

			const nextIssues = [ ...state.issues ];
			nextIssues.splice( index, 1, action.issue );

			return {
				...state,
				issues: nextIssues,
			};
		default:
			return state;
	}
}
