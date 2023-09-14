/**
 * External dependencies
 */
import { find, sortBy, random, trimEnd } from 'lodash';

/**
 * WordPress dependencies
 */
import { controls } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import { apiFetch, createNotice, awaitPromise, doAction } from '../controls';
import { STORE_NAME } from './';

export function* selectSiteType( id ) {
	const siteTypes = yield controls.resolveSelect(
		STORE_NAME,
		'getSiteTypes'
	);
	const siteType = find(
		siteTypes,
		( maybeSiteType ) => maybeSiteType.id === id
	);

	if ( ! siteType ) {
		throw __( 'No site type found with that id.', 'better-wp-security' );
	}

	yield receiveSiteType( siteType );
	yield resetOnboarding();
}

export function clearSiteType() {
	return { type: CLEAR_SITE_TYPE };
}

export function editAnswer( answer ) {
	return {
		type: EDIT_ANSWER,
		answer,
	};
}

export function* answerQuestion( answer ) {
	const request = yield controls.select(
		STORE_NAME,
		'getAnswerRequest',
		answer
	);
	let response;

	yield setIsAnswering();

	try {
		response = yield apiFetch( {
			method: 'PUT',
			path: `/ithemes-security/v1/site-types/${ request.id }`,
			data: request,
		} );
		yield receiveSiteType( response );
	} catch ( e ) {
		yield setError( e );
	}

	yield setIsAnswering( false );
}

export function* repeatQuestion() {
	const request = yield controls.select(
		STORE_NAME,
		'getRestoreSiteTypeRequest'
	);
	request.answers.pop();

	yield setIsAnswering();
	const response = yield apiFetch( {
		method: 'PUT',
		path: `/ithemes-security/v1/site-types/${ request.id }`,
		data: request,
	} );

	yield receiveSiteType( response );
	yield setIsAnswering( false );
}

export function* applyAnswerResponse() {
	const answers = yield controls.select( STORE_NAME, 'getAnswers' );
	const modules = yield controls.resolveSelect( MODULES_STORE_NAME, 'getModules' );

	for ( const answer of answers ) {
		for ( const module of answer.modules ) {
			const config = modules.find( ( { id } ) => id === module );

			if ( config?.side_effects ) {
				yield controls.dispatch( MODULES_STORE_NAME, 'activateModule', module );
			} else {
				yield controls.dispatch( MODULES_STORE_NAME, 'editModule', module, {
					status: {
						selected: 'active',
					},
				} );
			}
		}

		for ( const module in answer.settings ) {
			if ( answer.settings.hasOwnProperty( module ) ) {
				yield controls.dispatch(
					MODULES_STORE_NAME,
					'editSettings',
					module,
					answer.settings[ module ]
				);
			}
		}

		yield doAction( 'onboard.applyAnswerResponse', answer );
	}
}

export function* resetOnboarding() {
	yield editAnswer( null );
	yield controls.dispatch( MODULES_STORE_NAME, 'resetModuleEdits' );
	yield controls.dispatch( MODULES_STORE_NAME, 'resetSettingEdits' );
	yield doAction( 'onboard.reset' );
}

export function* completeOnboarding( { root } ) {
	const throwIf = ( maybeError ) => {
		if ( maybeError instanceof Error ) {
			throw maybeError;
		}
	};

	const steps = sortBy(
		yield controls.select( STORE_NAME, 'getCompletionSteps' ),
		'priority'
	);

	try {
		for ( const step of steps ) {
			if ( step.activeCallback && ! step.activeCallback( { root } ) ) {
				continue;
			}

			yield { type: SET_COMPLETION_STEP, step };
			const callback = step.callback();

			if ( callback instanceof Promise ) {
				throwIf( yield awaitPromise( callback, random( 1500, 2500 ) ) );
			}
		}

		yield controls.dispatch(
			MODULES_STORE_NAME,
			'editSetting',
			'global',
			'onboard_complete',
			true
		);
		yield controls.dispatch( MODULES_STORE_NAME, 'saveSettings', 'global' );

		yield { type: SET_COMPLETION_STEP, step: true };
	} catch ( error ) {
		yield { type: SET_COMPLETION_STEP, step: false };
		yield createNotice(
			'error',
			sprintf(
				/* translators: 1. Error message */
				__( 'Could not complete setup: %s', 'better-wp-security' ),
				error.message
			)
		);
	}
}

export function receiveSiteTypes( siteTypes ) {
	return {
		type: RECEIVE_SITE_TYPES,
		siteTypes,
	};
}

export function receiveSiteType( siteType ) {
	return {
		type: RECEIVE_SITE_TYPE,
		siteType,
	};
}

function setIsAnswering( isAnswering = true ) {
	return {
		type: SET_IS_ANSWERING,
		isAnswering,
	};
}

export function registerQuestionComponent( id, component ) {
	return {
		type: REGISTER_QUESTION_COMPONENT,
		id,
		component,
	};
}

export function registerCompletionStep( {
	id,
	label,
	priority,
	render,
	callback,
	activeCallback,
} ) {
	return {
		type: REGISTER_COMPLETION_STEP,
		id,
		label,
		priority,
		render,
		callback,
		activeCallback,
	};
}

export function recordVisitedLocation( location ) {
	return {
		type: RECORD_VISITED_LOCATION,
		location: trimEnd( location, '/' ),
	};
}

export function clearVisitedLocations() {
	return {
		type: CLEAR_VISITED_LOCATIONS,
	};
}

function setError( error ) {
	return {
		type: SET_ERROR,
		error,
	};
}

export const RECEIVE_SITE_TYPES = 'RECEIVE_SITE_TYPES';
export const RECEIVE_SITE_TYPE = 'RECEIVE_SITE_TYPE';
export const SELECT_SITE_TYPE = 'SELECT_SITE_TYPE';
export const CLEAR_SITE_TYPE = 'CLEAR_SITE_TYPE';
export const EDIT_ANSWER = 'EDIT_ANSWER';
export const SET_IS_ANSWERING = 'SET_IS_ANSWERING';
export const SET_ERROR = 'SET_ERROR';
export const REGISTER_QUESTION_COMPONENT = 'REGISTER_QUESTION_COMPONENT';
export const REGISTER_COMPLETION_STEP = 'REGISTER_COMPLETION_STEP';
export const SET_COMPLETION_STEP = 'SET_COMPLETION_STEP';
export const RECORD_VISITED_LOCATION = 'RECORD_VISITED_LOCATION';
export const CLEAR_VISITED_LOCATIONS = 'CLEAR_VISITED_LOCATIONS';
