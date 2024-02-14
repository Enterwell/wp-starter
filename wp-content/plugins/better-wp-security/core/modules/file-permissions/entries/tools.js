/**
 * WordPress dependencies
 */
import { setLocaleData } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';

// Silence warnings until JS i18n is stable.
setLocaleData( { '': {} }, 'ithemes-security-pro' );

/**
 * Internal dependencies
 */
import App from './tools/app.js';

registerPlugin( 'itsec-file-permissions-settings', {
	render() {
		return <App />;
	},
} );
