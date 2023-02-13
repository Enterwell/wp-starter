/**
 * External dependencies
 */
import { omit } from 'lodash';

/**
 * Internal dependencies
 */
import {
	FAILED_NOTICE_ACTION,
	FAILED_UPDATE_MUTED_HIGHLIGHT,
	FINISH_NOTICE_ACTION,
	FINISH_UPDATE_MUTED_HIGHLIGHT,
	RECEIVE_MUTED_HIGHLIGHTS,
	RECEIVE_NOTICES,
	START_NOTICE_ACTION,
	START_UPDATE_MUTED_HIGHLIGHT,
} from './actions';

const DEFAULT_STATE = {
	notices: [],
	doingActions: {},
	mutedHighlights: {},
	mutedHighlightUpdatesInFlight: {},
};

export default function adminNotices( state = DEFAULT_STATE, action ) {
	switch ( action.type ) {
		case RECEIVE_NOTICES:
			return {
				...state,
				notices: [
					...action.notices,
				],
			};
		case START_NOTICE_ACTION:
			return {
				...state,
				doingActions: {
					...state.doingActions,
					[ action.noticeId ]: [
						...( state.doingActions[ action.noticeId ] || [] ),
						action.actionId,
					],
				},
			};
		case FINISH_NOTICE_ACTION:
		case FAILED_NOTICE_ACTION:
			return {
				...state,
				doingActions: {
					...state.doingActions,
					[ action.noticeId ]: ( state.doingActions[ action.noticeId ] || [] ).filter( ( actionId ) => actionId !== action.actionId ),
				},
			};
		case RECEIVE_MUTED_HIGHLIGHTS:
			return {
				...state,
				mutedHighlights: action.mutedHighlights,
			};
		case START_UPDATE_MUTED_HIGHLIGHT:
			return {
				...state,
				mutedHighlightUpdatesInFlight: {
					...state.mutedHighlightUpdatesInFlight,
					[ action.slug ]: { mute: action.mute },
				},
			};
		case FINISH_UPDATE_MUTED_HIGHLIGHT:
			return {
				...state,
				mutedHighlightUpdatesInFlight: omit( state.mutedHighlightUpdatesInFlight, action.slug ),
				mutedHighlights: {
					...state.mutedHighlights,
					[ action.slug ]: action.mute,
				},
			};
		case FAILED_UPDATE_MUTED_HIGHLIGHT:
			return {
				...state,
				mutedHighlightUpdatesInFlight: omit( state.mutedHighlightUpdatesInFlight, action.slug ),
			};
		default:
			return state;
	}
}
