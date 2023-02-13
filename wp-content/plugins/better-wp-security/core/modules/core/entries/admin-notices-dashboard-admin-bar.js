/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import '@ithemes/security.core.admin-notices-api';
import AdminBar from './admin-notices/components/admin-bar';
registerPlugin( 'itsec-admin-notices-dashboard-admin-bar', {
	render: AdminBar,
} );
