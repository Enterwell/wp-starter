/**
 * WordPress dependencies
 */
import { registerStore } from '@wordpress/data';

import controls from '../controls';
import * as actions from './actions';
import * as selectors from './selectors';
import * as resolvers from './resolvers';
import reducer from './reducers';

export const STORE_NAME = 'ithemes-security/help';

const store = registerStore( STORE_NAME, {
	controls,
	actions,
	selectors,
	resolvers,
	reducer,
} );

export default store;
