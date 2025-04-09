/**
 * Internal dependencies
 */
import { apiFetch, doAction } from './controls';

export function receiveNotices( notices ) {
	return {
		type: RECEIVE_NOTICES,
		notices,
	};
}

export function startNoticeAction( noticeId, actionId ) {
	return {
		type: START_NOTICE_ACTION,
		noticeId,
		actionId,
	};
}

export function finishNoticeAction( noticeId, actionId, response ) {
	return {
		type: FINISH_NOTICE_ACTION,
		noticeId,
		actionId,
		response,
	};
}

export function failedNoticeAction( noticeId, actionId, error ) {
	return {
		type: FAILED_NOTICE_ACTION,
		noticeId,
		actionId,
		error,
	};
}

export function receiveMutedHighlights( mutedHighlights ) {
	return {
		type: RECEIVE_MUTED_HIGHLIGHTS,
		mutedHighlights,
	};
}

export function startUpdateMutedHighlight( slug, mute ) {
	return {
		type: START_UPDATE_MUTED_HIGHLIGHT,
		slug,
		mute,
	};
}

export function finishUpdateMutedHighlight( slug, mute ) {
	return {
		type: FINISH_UPDATE_MUTED_HIGHLIGHT,
		slug,
		mute,
	};
}

export function failedUpdateMutedHighlight( slug, mute, error ) {
	return {
		type: FAILED_UPDATE_MUTED_HIGHLIGHT,
		slug,
		mute,
		error,
	};
}

export function* doNoticeAction( noticeId, actionId, payload = {} ) {
	yield startNoticeAction( noticeId, actionId );
	yield doAction( 'admin-notices.triggerAction', noticeId, actionId, payload );

	let response;

	try {
		response = yield apiFetch( {
			path: `/ithemes-security/v1/admin-notices/${ noticeId }/${ actionId }`,
			method: 'POST',
			data: payload,
		} );
	} catch ( e ) {
		yield failedNoticeAction( noticeId, actionId, e );

		return e;
	}

	yield finishNoticeAction( noticeId, actionId, response );

	return response;
}

export function* updateMutedHighlight( slug, muted ) {
	yield startUpdateMutedHighlight( slug, muted );

	let response;

	try {
		response = yield apiFetch( {
			path: '/ithemes-security/v1/admin-notices/settings',
			method: 'PUT',
			data: {
				muted_highlights: {
					[ slug ]: muted,
				},
			},
		} );
	} catch ( e ) {
		yield failedUpdateMutedHighlight( slug, muted, e );

		return e;
	}

	yield finishUpdateMutedHighlight( slug, muted );

	return response;
}

export const RECEIVE_NOTICES = 'RECEIVE_NOTICES';
export const START_NOTICE_ACTION = 'START_NOTICE_ACTION';
export const FINISH_NOTICE_ACTION = 'FINISH_NOTICE_ACTION';
export const FAILED_NOTICE_ACTION = 'FAILED_NOTICE_ACTION';
export const RECEIVE_MUTED_HIGHLIGHTS = 'RECEIVE_MUTED_HIGHLIGHTS';
export const START_UPDATE_MUTED_HIGHLIGHT = 'START_UPDATE_MUTED_HIGHLIGHT';
export const FINISH_UPDATE_MUTED_HIGHLIGHT = 'FINISH_UPDATE_MUTED_HIGHLIGHT';
export const FAILED_UPDATE_MUTED_HIGHLIGHT = 'FAILED_UPDATE_MUTED_HIGHLIGHT';
