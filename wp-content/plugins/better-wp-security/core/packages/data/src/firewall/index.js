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
import reducer from './reducer';
import controls from '../controls';
import { STORE_NAME } from './constant';

const store = createReduxStore( STORE_NAME, {
	actions,
	selectors,
	resolvers,
	reducer,
	controls,
} );

register( store );

export default store;
