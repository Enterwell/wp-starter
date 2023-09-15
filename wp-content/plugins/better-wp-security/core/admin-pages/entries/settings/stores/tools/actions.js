/**
 * Internal dependencies
 */
import { Result } from '@ithemes/security-utils';
import { apiFetch, awaitPromise } from '../controls';

export function* fetchTools() {
	const response = yield apiFetch( {
		path: '/ithemes-security/v1/tools',
	} );

	yield { type: RECEIVE_TOOLS, tools: response };
}

export function* runTool( tool, form = {} ) {
	yield { type: START_TOOL, tool, form };
	let response;

	try {
		response = yield apiFetch( {
			path: `/ithemes-security/v1/tools/${ tool }`,
			method: 'POST',
			data: form,
			parse: false,
		} );
	} catch ( error ) {
		const result = yield awaitPromise(
			Result.fromResponse( error.getResponse() )
		);
		yield { type: FINISH_TOOL, tool, result };

		return result;
	}

	const result = yield awaitPromise( Result.fromResponse( response ) );
	yield { type: FINISH_TOOL, tool, result };

	return result;
}

export function* toggleTool( tool, enabled = true ) {
	yield { type: START_TOGGLE_TOOL, tool, enabled };
	let response;

	try {
		response = yield apiFetch( {
			path: `/ithemes-security/v1/tools/${ tool }`,
			method: 'PUT',
			data: {
				enabled,
			},
		} );
	} catch ( error ) {
		yield { type: FAILED_TOGGLE_TOOL, tool, error };

		return error;
	}

	yield { type: FINISH_TOGGLE_TOOL, tool, data: response };

	return response;
}

export const RECEIVE_TOOLS = 'RECEIVE_TOOLS';

export const START_TOOL = 'START_TOOL';
export const FINISH_TOOL = 'FINISH_TOOL';

export const START_TOGGLE_TOOL = 'START_TOGGLE_TOOL';
export const FAILED_TOGGLE_TOOL = 'FAILED_TOGGLE_TOOL';
export const FINISH_TOGGLE_TOOL = 'FINISH_TOGGLE_TOOL';
