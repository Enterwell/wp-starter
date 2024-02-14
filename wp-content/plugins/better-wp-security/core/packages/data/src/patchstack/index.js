/**
 * WordPress dependencies
 */
import { createReduxStore, register } from '@wordpress/data';

/**
 * Internal dependencies
 */
import controls from '../controls';
import * as actions from './actions';
import * as selectors from './selectors';
import * as resolvers from './resolvers';
import reducer from './reducers';
import { STORE_NAME } from './constant';

const store = createReduxStore( STORE_NAME, {
	controls,
	actions,
	selectors,
	resolvers,
	reducer,
} );

register( store );

export { STORE_NAME };
export default store;
