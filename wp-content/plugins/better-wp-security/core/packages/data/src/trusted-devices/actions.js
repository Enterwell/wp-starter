/**
 * External dependencies
 */
import { chunk, constant, isEqual, times } from 'lodash';

/**
 * WordPress dependencies
 */
import { addQueryArgs, getQueryArg } from '@wordpress/url';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import { getSelf, restUrlToPath } from '@ithemes/security-utils';
import { STORE_NAME as CORE_STORE_NAME } from '../constant';
import { path } from './constant';

export const query = ( queryId, userId, queryParams = {} ) => async ( { dispatch } ) => {
	let response, items;

	dispatch( { type: START_QUERY, queryId, userId, queryParams } );

	try {
		response = await apiFetch( {
			path: addQueryArgs( path + '/' + userId, queryParams ),
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
	const userId = select.getQueryUser( queryId );
	const queryParams = select.getQueryParams( queryId );
	dispatch.query( queryId, userId, queryParams );
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

/**
 * Saves multiple edited items in a batch.
 *
 * @param {string[]|true} items Either a list of item selves, or true to save all dirty items.
 * @return {function({dispatch: *, registry: *, select: *}): Promise<[]>} Action creator.
 */
export const saveEditedItems = ( items = true ) => async ( { dispatch, registry, select } ) => {
	if ( items === true ) {
		items = select.getDirtyItems();
	}

	const batch = items.map( ( self ) => ( {
		method: 'PUT',
		path: restUrlToPath( self ),
		body: select.getEditedItem( self ),
	} ) );

	items.forEach( ( self ) => dispatch( { type: 'START_SAVING', self } ) );

	const responses = await apiFetchBatch( registry, batch );
	for ( let i = 0; i < batch.length; i++ ) {
		const self = items[ i ];
		const response = responses[ i ];

		if ( response.status >= 400 ) {
			dispatch( { type: 'FAILED_SAVING', self, error: response.body } );
		} else {
			dispatch( { type: 'FINISH_SAVING', self } );
			dispatch( { type: RECEIVE_ITEM, item: response.body } );
		}
	}

	return responses;
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

async function apiFetchBatch( registry, batch ) {
	const maxItems = await registry
		.resolveSelect( CORE_STORE_NAME )
		.getBatchMaxItems();
	const chunks = chunk( batch, maxItems || 25 );
	const responses = [];

	if ( ! chunks.length ) {
		return [];
	}

	for ( const requests of chunks ) {
		try {
			const response = await apiFetch( {
				path: '/batch/v1',
				method: 'POST',
				data: { requests },
			} );
			responses.push( ...response.responses );
		} catch ( e ) {
			responses.push(
				...times(
					requests.length,
					constant( {
						body: e,
						status: 500,
						headers: {},
					} )
				)
			);
		}
	}

	return responses;
}

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
