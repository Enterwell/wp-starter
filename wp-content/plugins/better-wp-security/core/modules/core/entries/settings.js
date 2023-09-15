/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import '@ithemes/security.core.admin-notices-api';
import { ToolbarFill } from '@ithemes/security.pages.settings';
import ToolbarButton from './admin-notices/components/toolbar-button';

registerPlugin( 'itsec-admin-notices-settings-toolbar', {
	render() {
		return (
			<ToolbarFill>
				<ToolbarButton />
			</ToolbarFill>
		);
	},
} );
