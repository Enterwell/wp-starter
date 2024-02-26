/**
 * WordPress dependencies
 */
import { addQueryArgs, getQueryArg } from '@wordpress/url';

/**
 * Internal dependencies
 */
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

	return response;
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

/**
 * Runs a Site Scan.
 *
 * @param {number} [siteId] The site to scan.
 * @return {IterableIterator<*>} Iterator
 */
export function* runScan( siteId = 0 ) {
	yield { type: START_SCAN, siteId };

	try {
		const response = yield apiFetch( {
			path,
			method: 'POST',
			data: {
				site_id: siteId,
			},
		} );

		yield receiveScan( response );
		yield { type: FINISH_SCAN, siteId };

		return response;
	} catch ( error ) {
		yield { type: FAILED_SCAN, error };
		return error;
	}
}

export function receiveScan( scan ) {
	return {
		type: RECEIVE_SCAN,
		scan,
	};
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

export const RECEIVE_QUERY = 'RECEIVE_QUERY';

export const START_QUERY = 'START_QUERY';
export const FINISH_QUERY = 'FINISH_QUERY';
export const FAILED_QUERY = 'FAILED_QUERY';

export const RECEIVE_SCAN = 'RECEIVE_SCAN';

export const START_SCAN = 'START_SCAN';
export const FINISH_SCAN = 'FINISH_SCAN';
export const FAILED_SCAN = 'FAILED_SCAN';
