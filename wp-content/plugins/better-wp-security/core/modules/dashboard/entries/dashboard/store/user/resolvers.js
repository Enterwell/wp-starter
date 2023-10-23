/**
 * WordPress dependencies
 */
import { controls } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { receivePrimaryDashboard } from './actions';

export function* getPrimaryDashboard() {
	const user = yield controls.resolveSelect( 'ithemes-security/core', 'getCurrentUser' );
	yield receivePrimaryDashboard( user.meta._itsec_primary_dashboard );
}
