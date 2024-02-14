/**
 * WordPress dependencies
 */
import { addQueryArgs } from '@wordpress/url';
import apiFetch from '@wordpress/api-fetch';
import { path } from './constant';

export const getFirewallRules = () => async ( { dispatch } ) => {
	await dispatch.query( 'main', {
		per_page: 100,
		paused: 'false',
	} );
};

export const getEditedItem = {
	fulfill: ( self ) => async ( { dispatch } ) => {
		const item = await apiFetch( {
			url: addQueryArgs( self, { context: 'edit' } ),
		} );
		dispatch( { type: 'RECEIVE_ITEM', item } );
	},
	isFulfilled: ( state, self ) => state.query.bySelf[ self ]?.context === 'edit',
};

export const getItemById = {
	fulfill: ( id ) => async ( { dispatch } ) => {
		const response = await apiFetch( {
			path: `${ path }/${ id }?context=edit`,
		} );
		dispatch( { type: 'RECEIVE_ITEM', item: response } );
	},
	isFulfilled: ( state, id ) => !! state.query.selfById[ id ],
};
