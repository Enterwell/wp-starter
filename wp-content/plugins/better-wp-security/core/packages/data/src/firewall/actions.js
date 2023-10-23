/**
 * External dependencies
 */
import { isEqual } from 'lodash';

/**
 * WordPress dependencies
 */
import { addQueryArgs, getQueryArg } from '@wordpress/url';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import { getSelf } from '@ithemes/security-utils';
import { path } from './constant';

export const query = ( queryId, queryParams = {} ) => async ( { dispatch } ) => {
	let response, items;

	dispatch( { type: START_QUERY, queryId, queryParams } );

	try {
		response = await apiFetch( {
			path: addQueryArgs( path, queryParams ),
			parse: false,
		} );
		items = await response.json();
	} catch ( error ) {
		dispatch( { type: FAILED_QUERY, queryId, queryParams, error } );

		return error;
	}

	dispatch( receiveQuery(
		queryId,
		queryParams.context || 'view',
		response,
		items,
		'replace'
	) );
	dispatch( { type: FINISH_QUERY, queryId, queryParams, response } );

	return items;
};

export const refreshQuery = ( queryId ) => async ( { select, dispatch } ) => {
	const queryParams = select.getQueryParams( queryId );
	dispatch.query( queryId, queryParams );
};

export const fetchQueryPrevPage = ( queryId, mode = 'append' ) => ( ...args ) => {
	return fetchQueryLink( queryId, 'prev', mode )( ...args );
};

export const fetchQueryNextPage = ( queryId, mode = 'append' ) => ( ...args ) => {
	return fetchQueryLink( queryId, 'next', mode )( ...args );
};

const fetchQueryLink = ( queryId, rel, mode ) => async ( { select, dispatch } ) => {
	const link = select.getQueryHeaderLink(
		queryId,
		rel
	);

	if ( ! link ) {
		return [];
	}

	let response, items;

	dispatch( { type: START_QUERY, queryId } );

	try {
		response = await apiFetch( {
			url: link.link,
			parse: false,
		} );
		items = await response.json();
	} catch ( error ) {
		dispatch( { type: FAILED_QUERY, queryId, error } );

		return error;
	}

	const context = getQueryArg( link.link, 'context' ) || 'view';
	dispatch( receiveQuery( queryId, context, response, items, mode ) );
	dispatch( { type: FINISH_QUERY, queryId, response } );

	return response;
};

export const editItem = ( self, edit ) => async ( { select, dispatch } ) => {
	const item = select.getItem( self );
	const edited = select.getEditedItem( self );

	// If our edits are restoring us to the current state of the item,
	// just clear all the edits instead.
	if ( isEqual( item, { ...edited, ...edit } ) ) {
		dispatch( {
			type: 'RESET_EDITS',
			self,
		} );
	} else {
		dispatch( {
			type: 'EDIT_ITEM',
			self,
			edit,
		} );
	}
};

export function resetEdits( self ) {
	return {
		type: 'RESET_EDITS',
		self,
	};
}

export function resetAllEdits() {
	return {
		type: 'RESET_ALL_EDITS',
	};
}

export const saveEditedItem = ( self ) => async ( { select, dispatch } ) => {
	const edited = select.getEditedItem( self );

	if ( ! edited || ! select.isDirty( self ) ) {
		return edited;
	}

	const saved = await dispatch.saveItem( edited );

	dispatch( resetEdits( self ) );

	return saved;
};

export const saveItem = ( item ) => async ( { dispatch } ) => {
	const self = getSelf( item );

	if ( self ) {
		dispatch( { type: 'START_SAVING', self } );
	}

	try {
		const response = await apiFetch( {
			url: self,
			path: ! self && path,
			method: self ? 'PUT' : 'POST',
			data: item,
		} );
		dispatch( { type: 'RECEIVE_ITEM', item: response } );

		if ( self ) {
			dispatch( { type: 'FINISH_SAVING', self } );
		}

		return response;
	} catch ( error ) {
		if ( self ) {
			dispatch( { type: 'FAILED_SAVING', self, error } );
		}

		throw error;
	}
};

export const deleteItem = ( self ) => async ( { dispatch } ) => {
	dispatch( { type: 'START_DELETING', self } );

	try {
		await apiFetch( {
			url: self,
			method: 'DELETE',
		} );
		dispatch( { type: 'FINISH_DELETING', self } );
	} catch ( error ) {
		dispatch( { type: 'FAILED_DELETING', self, error } );
	}
};

function receiveQuery( queryId, context, response, items, mode ) {
	return {
		type: RECEIVE_QUERY,
		queryId,
		context,
		response,
		items,
		mode,
	};
}

export const RECEIVE_QUERY = 'RECEIVE_QUERY';
export const RECEIVE_ITEM = 'RECEIVE_ITEM';

export const START_QUERY = 'START_QUERY';
export const FINISH_QUERY = 'FINISH_QUERY';
export const FAILED_QUERY = 'FAILED_QUERY';
