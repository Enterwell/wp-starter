/**
 * WordPress dependencies
 */
import { controls } from '@wordpress/data';

export function receivePrimaryDashboard( dashboardId ) {
	return {
		type: RECEIVE_PRIMARY_DASHBOARD,
		dashboardId,
	};
}

export function* setPrimaryDashboard( dashboardId ) {
	yield controls.dispatch( 'ithemes-security/core', 'saveCurrentUser', {
		meta: {
			_itsec_primary_dashboard: dashboardId,
		},
	}, true );
	yield receivePrimaryDashboard( dashboardId );
}

export const RECEIVE_PRIMARY_DASHBOARD = 'RECEIVE_PRIMARY_DASHBOARD';
