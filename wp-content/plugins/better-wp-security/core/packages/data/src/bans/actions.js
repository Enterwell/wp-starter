/**
 * External dependencies
 */
import { isObject } from 'lodash';

/**
 * WordPress dependencies
 */
import { isURL, addQueryArgs, getQueryArg } from '@wordpress/url';

/**
 * Internal dependencies
 */
import { getSelf } from '@ithemes/security-utils';
import { select, apiFetch, parseFetchResponse } from '../controls';

export const path = '/ithemes-security/v1/bans';

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

export function *refreshQuery( queryId ) {
	const queryParams = yield select( 'ithemes-security/bans', 'getQueryParams', queryId );
	yield* query( queryId, queryParams );
}

export function* fetchQueryNextPage( queryId, mode = 'append' ) {
	const link = yield select(
		'ithemes-security/bans',
		'getQueryHeaderLink',
		queryId,
		'next'
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

export function receiveBan( ban ) {
	return {
		type: RECEIVE_BAN,
		ban,
	};
}

export function startCreateBan( ban ) {
	return { type: START_CREATE_BAN, ban };
}

export function failedCreateBan( ban, error ) {
	return { type: FAILED_CREATE_BAN, ban, error };
}

export function finishCreateBan( ban, response ) {
	return { type: FINISH_CREATE_BAN, ban, response };
}

export function startUpdateBan( self, ban ) {
	return { type: START_UPDATE_BAN, self, ban };
}

export function failedUpdateBan( self, error ) {
	return { type: FAILED_UPDATE_BAN, self, error };
}

export function finishUpdateBan( self, response ) {
	return { type: FINISH_UPDATE_BAN, self, response };
}

export function startDeleteBan( self ) {
	return { type: START_DELETE_BAN, self };
}

export function failedDeleteBan( self, error ) {
	return { type: FAILED_DELETE_BAN, self, error };
}

export function finishDeleteBan( self ) {
	return { type: FINISH_DELETE_BAN, self };
}

/**
 * Creates a new ban.
 *
 * @param {string} source The ban source or URL to the ban endpoint.
 * @param {Object} ban    Ban data.
 * @return {IterableIterator<*>} Iterator
 */
export function* createBan( source, ban ) {
	const request = {
		method: 'POST',
		data: ban,
	};

	if ( isURL( source ) ) {
		request.url = source;
	} else {
		request.path = `${ path }/${ source }`;
	}

	yield startCreateBan( ban );

	let response;

	try {
		response = yield apiFetch( request );
	} catch ( e ) {
		yield failedCreateBan( ban, e );
		return e;
	}

	yield finishCreateBan( ban, response );
	yield receiveBan( response );

	return response;
}

/**
 * Updates a ban.
 *
 * @param {Object|string} banOrSelf Ban object self link.
 * @param {Object}        update    Ban data.
 * @return {IterableIterator<*>} Iterator
 */
export function* updateBan( banOrSelf, update ) {
	const self = isObject( banOrSelf ) ? getSelf( banOrSelf ) : banOrSelf;
	yield startUpdateBan( self, update );

	let response;

	try {
		response = yield apiFetch( {
			url: self,
			method: 'PUT',
			data: update,
		} );
	} catch ( e ) {
		yield failedUpdateBan( self, e );
		return e;
	}

	yield finishUpdateBan( self, response );
	yield receiveBan( response );

	return response;
}

/**
 * Deletes a ban.
 *
 * @param {Object|string} banOrSelf Ban object or self link.
 * @return {IterableIterator<*>} Iterator
 */
export function* deleteBan( banOrSelf ) {
	const self = isObject( banOrSelf ) ? getSelf( banOrSelf ) : banOrSelf;

	yield startDeleteBan( self );

	try {
		yield apiFetch( {
			url: self,
			method: 'DELETE',
		} );
	} catch ( e ) {
		yield failedDeleteBan( self, e );
		return e;
	}

	yield finishDeleteBan( self );

	return null;
}

export const RECEIVE_QUERY = 'RECEIVE_QUERY';

export const START_QUERY = 'START_QUERY';
export const FINISH_QUERY = 'FINISH_QUERY';
export const FAILED_QUERY = 'FAILED_QUERY';

export const START_CREATE_BAN = 'START_CREATE_BAN';
export const FINISH_CREATE_BAN = 'FINISH_CREATE_BAN';
export const FAILED_CREATE_BAN = 'FAILED_CREATE_BAN';

export const RECEIVE_BAN = 'RECEIVE_BAN';

export const START_UPDATE_BAN = 'START_UPDATE_BAN';
export const FINISH_UPDATE_BAN = 'FINISH_UPDATE_BAN';
export const FAILED_UPDATE_BAN = 'FAILED_UPDATE_BAN';

export const START_DELETE_BAN = 'START_DELETE_BAN';
export const FINISH_DELETE_BAN = 'FINISH_DELETE_BAN';
export const FAILED_DELETE_BAN = 'FAILED_DELETE_BAN';
