/**
 * WordPress dependencies
 */
import { addQueryArgs, getQueryArg } from '@wordpress/url';
import { store as noticesStore } from '@wordpress/notices';

/**
 * Internal dependencies
 */
import { parseFetchResponse, select, apiFetch } from '../controls';
import { STORE_NAME, path, actionsPath } from './constant';
import { __ } from '@wordpress/i18n';
import { controls } from '@wordpress/data';

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

export function receiveUser( user ) {
	return {
		type: RECEIVE_USER,
		user,
	};
}

/**
 * Apply a series of actions to all (or some) users matching the given query.
 *
 * @param {string}           queryId Which users the quick action should be applied to
 * @param {'all' | 'window'} mode    All which applies to all users matching queryId or window to only affect the current pagination window
 * @param {Object}           actions A key-value map of actions to apply.
 */
export function* applyQuickActionsToQuery( queryId, mode, actions ) {
	const queryParams = yield select( STORE_NAME, 'getQueryParams', queryId );
	const id = queryId;
	yield { type: START_ACTION, id };

	try {
		const response = yield apiFetch( {
			path: actionsPath,
			method: 'POST',
			data: {
				query: queryParams,
				mode,
				actions,
			},
		} );
		yield { type: FINISH_ACTION, id };
		yield { type: CLOSE_QUICK_EDIT };
		yield { type: REMOVE_SELECTED_USERS };
		yield quickEditsSuccessSnackbar();

		return response;
	} catch ( error ) {
		yield { type: FAILED_ACTION, id, error };
		return error;
	}
}

/**
 * Apply a series of actions to a list of specific user ids.
 *
 * @param {number[]} users   The list of user ids.
 * @param {Object}   actions A key-value map of actions to apply.
 * @param {string}   id      A unique identifier to track this action's status.
 */
export function* applyQueryActionsToUsers( users, actions, id ) {
	yield { type: START_ACTION, id };
	try {
		const response = yield apiFetch( {
			path: actionsPath,
			method: 'POST',
			data: {
				users,
				actions,
			},
		} );
		yield { type: FINISH_ACTION, id };
		yield { type: CLOSE_QUICK_EDIT };
		yield { type: REMOVE_SELECTED_USERS };
		yield quickEditsSuccessSnackbar();

		return response;
	} catch ( error ) {
		yield { type: FAILED_ACTION, id, error };
		return error;
	}
}

export function quickEditsSuccessSnackbar() {
	return controls.dispatch(
		noticesStore,
		'createNotice',
		'info',
		__( 'Quick edits are being applied in the background. This may take a few moments.', 'better-wp-security' ),
		{
			id: 'user_security_quick_edits_success',
			type: 'snackbar',
			context: 'ithemes-security',
		}
	);
}

/**
 * Apply the submitted selection type
 *
 * @param {string} userSelectionType The new type to use
 * @return {string} Returns the new selection type
 */
export function* updateUserSelectionType( userSelectionType ) {
	switch ( userSelectionType ) {
		case ( 'all' ):
			yield controls.dispatch(
				noticesStore,
				'createNotice',
				'info',
				__( 'All users from the query are selected', 'better-wp-security' ),
				{
					id: 'user_security_all_selected',
					type: 'snackbar',
					context: 'ithemes-security',
				}

			);
			break;
		case ( 'window' ):
			yield controls.dispatch(
				noticesStore,
				'createNotice',
				'info',
				__( 'Only the current page of users from the query are selected', 'better-wp-security' ),
				{
					id: 'user_security_page_selected',
					type: 'snackbar',
					context: 'ithemes-security',
				}
			);
			break;
		case ( 'none' ):
			yield controls.dispatch(
				noticesStore,
				'createNotice',
				'info',
				__( 'None of the users from the query are selected', 'better-wp-security' ),
				{
					id: 'user_security_none_selected',
					type: 'snackbar',
					context: 'ithemes-security',
				}
			);
			yield { type: REMOVE_SELECTED_USERS };
			break;
	}
	yield { type: UPDATE_SELECTION_TYPE, userSelectionType };
}

export function toggleSelectAll( ) {
	return { type: TOGGLE_SELECT_ALL };
}

export function toggleSelectedUser( user ) {
	return { type: TOGGLE_SELECTED_USER, user };
}

export function removeSelectedUsers() {
	return { type: REMOVE_SELECTED_USERS };
}

export function openQuickEdit( ) {
	return { type: OPEN_QUICK_EDIT };
}

export function closeQuickEdit() {
	return { type: CLOSE_QUICK_EDIT };
}

export function confirmQuickEdit() {
	return { type: CONFIRM_QUICK_EDIT };
}

export const RECEIVE_QUERY = 'RECEIVE_QUERY';
export const START_QUERY = 'START_QUERY';
export const FINISH_QUERY = 'FINISH_QUERY';
export const FAILED_QUERY = 'FAILED_QUERY';
export const START_ACTION = 'START_ACTION';
export const FINISH_ACTION = 'FINISH_ACTION';
export const FAILED_ACTION = 'FAILED_ACTION';
export const RECEIVE_USER = 'RECEIVE_USER';
export const UPDATE_SELECTION_TYPE = 'UPDATE_SELECTION_TYPE';
export const TOGGLE_SELECTED_USER = 'TOGGLE_SELECTED_USER';
export const TOGGLE_SELECT_ALL = 'TOGGLE_SELECT_ALL';
export const OPEN_QUICK_EDIT = 'OPEN_QUICK_EDIT';
export const CLOSE_QUICK_EDIT = 'CLOSE_QUICK_EDIT';
export const CONFIRM_QUICK_EDIT = 'CONFIRM_QUICK_EDIT';
export const REMOVE_SELECTED_USERS = 'REMOVE_SELECTED_USERS';
