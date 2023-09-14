/**
 * WordPress dependencies
 */
import { registerStore } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME as BANS_STORE_NAME } from './bans';
import { STORE_NAME as HELP_STORE_NAME } from './help';
import { STORE_NAME as MODULES_STORE_NAME } from './modules';
import controls from './controls';
import * as selectors from './selectors';
import * as resolvers from './resolvers';
import * as actions from './actions';
import reducer from './reducers';

const CORE_STORE_NAME = 'ithemes-security/core';

registerStore( CORE_STORE_NAME, {
	controls,
	selectors,
	resolvers,
	actions,
	reducer,
} );

export {
	controls,
	CORE_STORE_NAME,
	BANS_STORE_NAME,
	HELP_STORE_NAME,
	MODULES_STORE_NAME,
};

export * from './controls';
