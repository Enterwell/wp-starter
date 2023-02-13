/**
 * WordPress dependencies
 */
import { registerStore } from '@wordpress/data';

/**
 * Internal dependencies
 */
import controls from './controls';
import * as selectors from './selectors';
import * as resolvers from './resolvers';
import * as actions from './actions';
import reducer from './reducers';

registerStore( 'ithemes-security/core', {
	controls,
	selectors,
	resolvers,
	actions,
	reducer,
} );
