/**
 * WordPress dependencies
 */
import { addQueryArgs, getQueryArg } from '@wordpress/url';
import { controls } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { getLink, getSelf } from '@ithemes/security-utils';
import { select, apiFetch, parseFetchResponse } from '../controls';
import { STORE_NAME, path } from './constant';

export function* query( queryId, queryParams = {} ) {
	let response, items;

	yield { type: START_QUERY, queryId, queryParams };

	try {
		response = yield apiFetch( {
			path: addQueryArgs( path, queryParams ),
			parse: false,
		} );
		items = yield parseFetchResponse( response );
	} catch ( error ) {
		yield { type: FAILED_QUERY, queryId, queryParams, error };

		return error;
	}

	yield receiveQuery(
		queryId,
		queryParams.context || 'view',
		response,
		items,
		'replace'
	);
	yield { type: FINISH_QUERY, queryId, queryParams, response };

	return items;
}

export function* refreshQuery( queryId ) {
	const queryParams = yield select( STORE_NAME, 'getQueryParams', queryId );
	yield* query( queryId, queryParams );
}

export function* fetchQueryPrevPage( queryId, mode = 'append' ) {
	return yield * fetchQueryLink( queryId, 'prev', mode );
}

export function* fetchQueryNextPage( queryId, mode = 'append' ) {
	return yield * fetchQueryLink( queryId, 'next', mode );
}

function* fetchQueryLink( queryId, rel, mode ) {
	const link = yield select(
		STORE_NAME,
		'getQueryHeaderLink',
		queryId,
		rel
	);

	if ( ! link ) {
		return [];
	}

	let response, items;

	yield { type: START_QUERY, queryId };

	try {
		response = yield apiFetch( {
			url: link.link,
			parse: false,
		} );
		items = yield parseFetchResponse( response );
	} catch ( error ) {
		yield { type: FAILED_QUERY, queryId, error };

		return error;
	}

	const context = getQueryArg( link.link, 'context' ) || 'view';
	yield receiveQuery( queryId, context, response, items, mode );
	yield { type: FINISH_QUERY, queryId, response };

	return response;
}

export function* muteVulnerability( vulnerabilityOrSelf ) {
	return yield* applyVulnerabilityAction( vulnerabilityOrSelf, 'ithemes-security:mute-vulnerability' );
}

export function* fixVulnerability( vulnerabilityOrSelf ) {
	return yield* applyVulnerabilityAction( vulnerabilityOrSelf, 'ithemes-security:fix-vulnerability' );
}

export function* deactivateSoftware( vulnerabilityOrSelf ) {
	return yield* applyVulnerabilityAction( vulnerabilityOrSelf, 'ithemes-security:deactivate-vulnerable-software' );
}

export function* applyVulnerabilityAction( vulnerabilityOrSelf, rel ) {
	const vulnerability = typeof vulnerabilityOrSelf === 'string'
		? ( yield controls.select( STORE_NAME, 'getVulnerability', vulnerabilityOrSelf ) )
		: vulnerabilityOrSelf;

	const actionLink = getLink( vulnerability, rel );

	if ( ! actionLink ) {
		return;
	}

	const self = getSelf( vulnerability );
	yield { type: START_ACTION, rel, self };

	try {
		const response = yield apiFetch( {
			url: actionLink,
			method: 'POST',
		} );
		yield { type: FINISH_ACTION, rel, self };
		yield receiveVulnerability( response );

		return response;
	} catch ( error ) {
		yield { type: FAILED_ACTION, rel, self, error };
		return error;
	}
}

export function receiveQuery( queryId, context, response, items, mode ) {
	return {
		type: RECEIVE_QUERY,
		queryId,
		context,
		response,
		items,
		mode,
	};
}

export function receiveVulnerability( vulnerability ) {
	return {
		type: RECEIVE_VULNERABILITY,
		vulnerability,
	};
}

export const RECEIVE_QUERY = 'RECEIVE_QUERY';

export const START_QUERY = 'START_QUERY';
export const FINISH_QUERY = 'FINISH_QUERY';
export const FAILED_QUERY = 'FAILED_QUERY';

export const START_ACTION = 'START_ACTION';
export const FINISH_ACTION = 'FINISH_ACTION';
export const FAILED_ACTION = 'FAILED_ACTION';

export const RECEIVE_VULNERABILITY = 'RECEIVE_VULNERABILITY';
