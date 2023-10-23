/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data';

/**
 * Returns true if resolution is in progress for the core selector of the given
 * name and arguments.
 *
 * @param {string} selectorName Core data selector name.
 * @param {...*}   args         Arguments passed to selector.
 *
 * @return {boolean} Whether resolution is in progress.
 */
export function isResolving( selectorName, ...args ) {
	return select( 'core/data' ).isResolving(
		'ithemes-security/admin-notices',
		selectorName,
		args
	);
}

export function isResolved( selectorName, ...args ) {
	return select( 'core/data' ).hasFinishedResolution(
		'ithemes-security/admin-notices',
		selectorName,
		args
	);
}

export function getNotices( state ) {
	return state.notices;
}

export function isNoticeDismissed( state, noticeId ) {
	return ! state.notices.find( ( notice ) => notice.id === noticeId );
}

export function areNoticesLoaded() {
	return isResolved( 'getNotices' );
}

export function isDoingAction( state, noticeId, actionId = '' ) {
	if ( ! state.doingActions[ noticeId ] ) {
		return false;
	}

	if ( actionId === '' ) {
		return true;
	}

	return state.doingActions[ noticeId ].includes( actionId );
}

const DEFAULT_IN_PROGRESS = [];

export function getInProgressActions( state, noticeId ) {
	return state.doingActions[ noticeId ] || DEFAULT_IN_PROGRESS;
}

export function getMutedHighlights( state ) {
	return state.mutedHighlights;
}

export function getMutedHighlightUpdatesInFlight( state ) {
	return state.mutedHighlightUpdatesInFlight;
}
