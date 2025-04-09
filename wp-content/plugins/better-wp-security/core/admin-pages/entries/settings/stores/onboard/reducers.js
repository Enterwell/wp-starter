/**
 * External dependencies
 */
import { omit, without, last } from 'lodash';

/**
 * Internal dependencies
 */
import {
	RECEIVE_SITE_TYPES,
	RECEIVE_SITE_TYPE,
	SELECT_SITE_TYPE,
	SET_IS_ANSWERING,
	REGISTER_QUESTION_COMPONENT,
	CLEAR_SITE_TYPE,
	SET_COMPLETION_STEP,
	REGISTER_COMPLETION_STEP,
	EDIT_ANSWER,
	SET_ERROR,
	RECORD_VISITED_LOCATION,
	CLEAR_VISITED_LOCATIONS,
} from './actions';

const DEFAULT_STATE = {
	siteTypes: [],
	selectedSiteType: '',
	answers: [],
	nextQuestion: undefined,
	editedAnswer: undefined,
	lastError: undefined,
	isAnswering: false,
	questionComponents: {},
	completionStep: false,
	completionSteps: {},
	visitedLocations: [],
};

export default function( state = DEFAULT_STATE, action ) {
	switch ( action.type ) {
		case RECEIVE_SITE_TYPES:
			return {
				...state,
				siteTypes: action.siteTypes,
			};
		case RECEIVE_SITE_TYPE:
			return {
				...state,
				selectedSiteType: action.siteType.id,
				nextQuestion: action.siteType.next_question,
				answers: action.siteType.answers,
				editedAnswer:
					action.siteType.next_question?.answer_schema?.default,
				lastError: undefined,
			};
		case SELECT_SITE_TYPE:
			return {
				...state,
				selectedSiteType: action.id,
				answers: [],
				nextQuestion: undefined,
				lastError: undefined,
			};
		case CLEAR_SITE_TYPE:
			return {
				...state,
				selectedSiteType: '',
				answers: [],
				nextQuestion: undefined,
				lastError: undefined,
			};
		case EDIT_ANSWER:
			return {
				...state,
				editedAnswer: action.answer,
			};
		case SET_IS_ANSWERING:
			return {
				...state,
				isAnswering: action.isAnswering,
			};
		case SET_ERROR:
			return {
				...state,
				lastError: action.error,
			};
		case SET_COMPLETION_STEP:
			return {
				...state,
				completionStep: action.step,
			};
		case REGISTER_QUESTION_COMPONENT:
			return {
				...state,
				questionComponents: {
					...state.questionComponents,
					[ action.id ]: action.component,
				},
			};
		case REGISTER_COMPLETION_STEP:
			return {
				...state,
				completionSteps: {
					...state.completionSteps,
					[ action.id ]: omit( action, [ 'type' ] ),
				},
			};
		case RECORD_VISITED_LOCATION:
			return {
				...state,
				visitedLocations:
					last( state.visitedLocations ) === action.location
						? state.visitedLocations
						: without(
							state.visitedLocations,
							action.location
						).concat( action.location ),
			};
		case CLEAR_VISITED_LOCATIONS:
			return {
				...state,
				visitedLocations: [],
			};
		default:
			return state;
	}
}
