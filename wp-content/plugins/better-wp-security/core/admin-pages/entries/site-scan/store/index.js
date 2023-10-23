/**
 * WordPress dependencies
 */
import { createReduxStore, register } from '@wordpress/data';

/**
 * Internal dependencies
 */
import * as actions from './actions';
import * as selectors from './selectors';
import reducer from './reducer';
import { STORE_NAME } from './constant';

const store = createReduxStore( STORE_NAME, {
	actions,
	selectors,
	reducer,
} );

register( store );

export default store;
