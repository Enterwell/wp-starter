/**
 * WordPress dependencies
 */
import { setLocaleData } from '@wordpress/i18n';
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

// Silence warnings until JS i18n is stable.
setLocaleData( { '': {} }, 'better-wp-security' );

/**
 * Internal dependencies
 */
import App from './admin-notices/app.js';

domReady( () => {
	const containerEl = document.getElementById( 'wp-admin-bar-itsec_admin_bar_menu' );
	const portalEl = document.getElementById( 'itsec-admin-notices-root' );

	return render( <App portalEl={ portalEl } />, containerEl );
} );
