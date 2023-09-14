/**
 * WordPress dependencies
 */
import { registerStore } from '@wordpress/data';

/**
 * Internal dependencies
 */
import controls from '../controls';
import * as actions from './actions';
import * as selectors from './selectors';
import reducer from './reducers';
import * as resolvers from './resolvers';

export const STORE_NAME = 'ithemes-security/tools';

const store = registerStore( STORE_NAME, {
	controls,
	actions,
	selectors,
	resolvers,
	reducer,
} );

export default store;
