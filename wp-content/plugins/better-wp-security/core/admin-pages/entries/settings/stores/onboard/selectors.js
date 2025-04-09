/**
 * External dependencies
 */
import { find } from 'lodash';

export function getSiteTypes( state ) {
	return state.siteTypes;
}

export function getSelectedSiteTypeId( state ) {
	return state.selectedSiteType;
}

export function getSelectedSiteType( state ) {
	return find( getSiteTypes( state ), {
		id: getSelectedSiteTypeId( state ),
	} );
}

export function getAnswers( state ) {
	return state.answers;
}

export function getNextQuestion( state ) {
	return state.nextQuestion;
}

export function getEditedAnswer( state ) {
	return state.editedAnswer;
}

export function isAnswering( state ) {
	return state.isAnswering;
}

export function getLastError( state ) {
	return state.lastError;
}

export function getQuestionComponent( state, questionId ) {
	return state.questionComponents[ questionId ];
}

export function getCompletionSteps( state ) {
	return state.completionSteps;
}

export function getAnswerRequest( state, answer ) {
	const id = state.selectedSiteType;
	const answers = state.answers;
	const nextQuestion = state.nextQuestion;

	return {
		id,
		answers: [
			...answers,
			{
				question: nextQuestion.id,
				answer,
			},
		],
	};
}

export function getRestoreSiteTypeRequest( state ) {
	const id = state.selectedSiteType;
	const answers = state.answers;

	return {
		id,
		answers: [ ...answers ],
	};
}

export function getLastVisitedLocation( state ) {
	const ignore = [ '/onboard', '/onboard/site-type' ];

	for ( let i = state.visitedLocations.length - 1; i >= 0; i-- ) {
		const path = state.visitedLocations[ i ];

		if ( ! ignore.includes( path ) ) {
			return path;
		}
	}
}

/**
 * Gets the current completion step.
 *
 * @param {Object} state
 * @return {boolean|{id: string, label: string}} True if completed, false if not started. Object describing the current step otherwise.
 */
export function getCompletionStep( state ) {
	return state.completionStep;
}
