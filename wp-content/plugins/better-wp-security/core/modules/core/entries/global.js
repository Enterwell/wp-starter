/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import '@ithemes/security.core.admin-notices-api';
import { ToolbarFill } from '@ithemes/security-ui';
import ToolbarButton from './admin-notices/components/toolbar-button';
import SolidWelcome from './solid-welcome/app.js';

registerPlugin( 'itsec-admin-notices-toolbar', {
	render() {
		return (
			<ToolbarFill>
				<ToolbarButton />
			</ToolbarFill>
		);
	},
} );

registerPlugin( 'itsec-solid-welcome', {
	render() {
		return (
			<SolidWelcome />
		);
	},
} );
