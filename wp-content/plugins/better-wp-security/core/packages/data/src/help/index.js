/**
 * WordPress dependencies
 */
import { createReduxStore, register } from '@wordpress/data';

import controls from '../controls';
import * as actions from './actions';
import * as selectors from './selectors';
import * as resolvers from './resolvers';
import reducer from './reducers';

export const STORE_NAME = 'ithemes-security/help';

const store = createReduxStore( STORE_NAME, {
	controls,
	actions,
	selectors,
	resolvers,
	reducer,
} );

register( store );

export default store;
