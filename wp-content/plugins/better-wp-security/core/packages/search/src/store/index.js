/**
 * WordPress dependencies
 */
import { registerStore } from '@wordpress/data';

/**
 * Internal dependencies
 */
import * as actions from './actions';
import * as selectors from './selectors';
import reducer from './reducers';

export const STORE_NAME = 'ithemes-security/search';

const store = registerStore( STORE_NAME, {
	actions,
	selectors,
	reducer,
} );

export default store;
