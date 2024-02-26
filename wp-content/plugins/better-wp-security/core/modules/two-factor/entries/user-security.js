/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';
import { setLocaleData } from '@wordpress/i18n';

// Silence warnings until JS i18n is stable.
setLocaleData( { '': {} }, 'ithemes-security-pro' );

/**
 * Internal dependencies
 */
import App from './user-security/app.js';

registerPlugin( 'itsec-two-factor-user-security', {
	render() {
		return <App />;
	},
} );
