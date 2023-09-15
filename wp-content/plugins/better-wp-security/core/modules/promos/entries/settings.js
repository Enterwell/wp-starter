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
import App from './settings/app.js';

registerPlugin( 'itsec-promos-settings', {
	render() {
		return <App />;
	},
} );
