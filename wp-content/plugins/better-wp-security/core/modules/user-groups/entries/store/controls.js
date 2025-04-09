/**
 * WordPress dependencies
 */
import { default as triggerApiFetch } from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import { responseToError } from '@ithemes/security-utils';

/**
 * Trigger an API Fetch request.
 *
 * @param {Object} request API Fetch Request Object.
 * @return {Object} control descriptor.
 */
export function apiFetch( request ) {
	return {
		type: 'API_FETCH',
		request,
	};
}

const controls = {
	API_FETCH( { request } ) {
		return triggerApiFetch( request ).catch( responseToError );
	},
};

export default controls;
