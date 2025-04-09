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
import reducer from './reducers';
import * as resolvers from './resolvers';
import { STORE_NAME } from './constant';

export { STORE_NAME };

const store = createReduxStore( STORE_NAME, {
	controls,
	actions,
	selectors,
	resolvers,
	reducer,
	persist: [ 'moduleEdits', 'settingEdits' ],
} );

register( store );

export default store;
