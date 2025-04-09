/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import '@ithemes/security.core.admin-notices-api';
import { AdminBarFill } from '@ithemes/security.dashboard.api';
import ToolbarButton from './admin-notices/components/toolbar-button';

registerPlugin( 'itsec-admin-notices-dashboard-admin-bar', {
	render() {
		return (
			<AdminBarFill>
				<ToolbarButton />
			</AdminBarFill>
		);
	},
} );
