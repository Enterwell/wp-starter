/**
 * WordPress dependencies
 */
import { registerStore } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { controls as dataControls } from '@ithemes/security.packages.data';
import controls from './controls';
import * as actions from './actions';
import * as selectors from './selectors';
import userGroups from './reducers';
import * as resolvers from './resolvers';

const store = registerStore( 'ithemes-security/user-groups', {
	controls: { ...dataControls, ...controls },
	actions,
	selectors,
	resolvers,
	reducer: userGroups,
} );

export default store;
