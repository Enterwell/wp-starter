/**
 * WordPress dependencies
 */
import { register, createReduxStore } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { controls as dataControls } from '@ithemes/security.packages.data';
import controls from './controls';
import * as actions from './actions';
import * as selectors from './selectors';
import userGroups from './reducers';
import * as resolvers from './resolvers';
import { STORE_NAME } from './constant';

const store = createReduxStore( STORE_NAME, {
	controls: { ...dataControls, ...controls },
	actions,
	selectors,
	resolvers,
	reducer: userGroups,
} );

register( store );
export default store;
