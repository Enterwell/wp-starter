/**
 * WordPress dependencies
 */
import { createReduxStore, register } from '@wordpress/data';

/**
 * Internal dependencies
 */
import * as actions from './actions';
import * as selectors from './selectors';
import * as resolvers from './resolvers';
import controls from './../controls';
import reducer from './reducer';
import { STORE_NAME } from './constant';

const store = createReduxStore(
	STORE_NAME, {
		actions,
		selectors,
		resolvers,
		controls,
		reducer,
	} );

register( store );

export default store;
