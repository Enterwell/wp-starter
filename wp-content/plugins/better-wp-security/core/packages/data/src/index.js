/**
 * WordPress dependencies
 */
import { createReduxStore, register } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { STORE_NAME as BANS_STORE_NAME } from './bans';
import { STORE_NAME as HELP_STORE_NAME } from './help';
import modulesStore, { STORE_NAME as MODULES_STORE_NAME } from './modules';
import toolsStore from './tools';
import vulnerabilitiesStore from './vulnerabilities';
import siteScannerStore from './site-scanner';
import patchstackStore from './patchstack';
import userSecurityStore from './user-security';
import firewallStore from './firewall';
import logsStore from './logs';
import trustedDevicesStore from './trusted-devices';
import controls from './controls';
import * as selectors from './selectors';
import * as resolvers from './resolvers';
import * as actions from './actions';
import reducer from './reducers';
import { STORE_NAME as CORE_STORE_NAME } from './constant';

const store = createReduxStore( CORE_STORE_NAME, {
	controls,
	selectors,
	resolvers,
	actions,
	reducer,
} );

register( store );

export {
	controls,
	CORE_STORE_NAME,
	BANS_STORE_NAME,
	HELP_STORE_NAME,
	MODULES_STORE_NAME,
	store as coreStore,
	modulesStore,
	toolsStore,
	vulnerabilitiesStore,
	siteScannerStore,
	patchstackStore,
	userSecurityStore,
	firewallStore,
	logsStore,
	trustedDevicesStore,
};

export * from './controls';
