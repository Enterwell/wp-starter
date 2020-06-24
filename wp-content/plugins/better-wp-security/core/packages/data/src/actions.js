/**
 * Internal dependencies
 */
import { apiFetch } from './controls';

/**
 * Fetch the index.
 *
 * @param {boolean} breakCache Whether to break the cache or not.
 * @return {Object} The index.
 */
export function* fetchIndex( breakCache = false ) {
	let path = '/ithemes-security/v1?context=help';

	if ( breakCache ) {
		path += '&_=' + Date.now();
	}

	const index = yield apiFetch( { path } );
	yield receiveIndex( index );

	return index;
}

export function receiveIndex( index ) {
	return {
		type: RECEIVE_INDEX,
		index,
	};
}

export function receiveUser( user ) {
	return {
		type: RECEIVE_USER,
		user,
	};
}

export const RECEIVE_INDEX = 'RECEIVE_INDEX';
export const RECEIVE_USER = 'RECEIVE_USER';
