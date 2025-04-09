/**
 * WordPress dependencies
 */
import { controls } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { apiFetch } from '../controls';
import { receiveSiteTypes, receiveSiteType } from './actions';
import { STORE_NAME } from './';

export const getSiteTypes = {
	*fulfill() {
		const siteTypes = yield apiFetch( {
			path: '/ithemes-security/v1/site-types',
		} );
		yield receiveSiteTypes( siteTypes );
	},
	isFulfilled( state ) {
		return state.siteTypes.length > 0;
	},
};

export const getSelectedSiteType = {
	*fulfill() {
		yield controls.resolveSelect( STORE_NAME, 'getSiteTypes' );
	},
	isFulfilled( state ) {
		return state.siteTypes.length > 0;
	},
};

export const getNextQuestion = {
	*fulfill() {
		const request = yield controls.select(
			STORE_NAME,
			'getRestoreSiteTypeRequest'
		);
		const response = yield apiFetch( {
			method: 'PUT',
			path: `/ithemes-security/v1/site-types/${ request.id }`,
			data: request,
		} );
		yield receiveSiteType( response );
	},
	isFulfilled( state ) {
		return state.nextQuestion !== undefined;
	},
};
