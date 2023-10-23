/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';
import { __, setLocaleData } from '@wordpress/i18n';
import { dispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

// Silence warnings until JS i18n is stable.
setLocaleData( { '': {} }, 'ithemes-security-pro' );

/**
 * Internal dependencies
 */
import { store } from '@ithemes/security.pages.site-scan';
import App from './site-scan/app.js';

registerPlugin( 'itsec-two-factor-site-scan', {
	render() {
		return <App />;
	},
} );

dispatch( store ).registerScanComponent( {
	slug: 'two-factor',
	priority: 19,
	label: __( 'Two Factor', 'better-wp-security' ),
	description: __( 'Check for users without two-factor enabled.', 'better-wp-security' ),
	async execute() {
		return apiFetch( {
			method: 'GET',
			path: '/ithemes-security/v1/two-factor/scan',
		} );
	},
} );

