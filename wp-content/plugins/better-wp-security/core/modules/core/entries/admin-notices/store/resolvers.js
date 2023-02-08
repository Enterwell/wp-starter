/**
 * External dependencies
 */
import { isEmpty } from 'lodash';

/**
 * Internal dependencies
 */
import { apiFetch } from './controls';
import { FINISH_NOTICE_ACTION, FINISH_UPDATE_MUTED_HIGHLIGHT, receiveMutedHighlights, receiveNotices } from './actions';

const getNotices = {
	*fulfill() {
		const notices = yield apiFetch( {
			path: '/ithemes-security/v1/admin-notices',
		} );

		yield receiveNotices( notices );
	},
	shouldInvalidate( action ) {
		return action.type === FINISH_NOTICE_ACTION || action.type === FINISH_UPDATE_MUTED_HIGHLIGHT;
	},
};

export { getNotices as getNotices };

export function *getMutedHighlights() {
	const settings = yield apiFetch( {
		path: '/ithemes-security/v1/admin-notices/settings',
	} );

	yield receiveMutedHighlights( isEmpty( settings.muted_highlights ) ? {} : settings.muted_highlights );
}
