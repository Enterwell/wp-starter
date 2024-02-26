/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */
import { addQueryArgs, getQueryArg } from '@wordpress/url';
import { controls } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { fetch, parseFetchResponse } from '../controls.js';
import { path, STORE_NAME } from './constant.js';

export function* query( queryId, queryParams = {} ) {
	let response, items;

	yield { type: START_QUERY, queryId, queryParams };

	queryParams.per_page = queryParams.per_page || 100;
	queryParams.page = queryParams.page || 1;

	try {
		response = yield fetch( addQueryArgs( path + '/db', queryParams ), {
			credentials: 'omit',
		} );
		if ( response.ok ) {
			items = yield parseFetchResponse( response );
		} else {
			throw yield parseFetchResponse( response );
		}
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
	const queryParams = yield controls.select( STORE_NAME, 'getQueryParams', queryId );
	yield* query( queryId, queryParams );
}

export function* fetchQueryPrevPage( queryId, mode = 'append' ) {
	return yield * fetchQueryLink( queryId, 'prev', mode );
}

export function* fetchQueryNextPage( queryId, mode = 'append' ) {
	return yield * fetchQueryLink( queryId, 'next', mode );
}

function* fetchQueryLink( queryId, rel, mode ) {
	const link = yield controls.select(
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
		response = yield fetch( path + link.link, {
			credentials: 'omit',
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

export const START_QUERY = 'START_QUERY';
export const FINISH_QUERY = 'FINISH_QUERY';
export const FAILED_QUERY = 'FAILED_QUERY';
export const RECEIVE_QUERY = 'RECEIVE_QUERY';
