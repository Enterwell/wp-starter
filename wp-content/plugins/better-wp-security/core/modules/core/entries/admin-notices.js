/**
 * WordPress dependencies
 */
import { setLocaleData } from '@wordpress/i18n';
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import { addAction } from '@wordpress/hooks';

// Silence warnings until JS i18n is stable.
setLocaleData( { '': {} }, 'better-wp-security' );

/**
 * Internal dependencies
 */
import { App as SolidWelcome } from '@ithemes/security.core.solid-welcome';
import App from './admin-notices/app.js';

domReady( () => {
	const containerEl = document.getElementById(
		'wp-admin-bar-itsec_admin_bar_menu'
	);
	const portalEl = document.getElementById( 'itsec-admin-notices-root' );

	return render( <App portalEl={ portalEl } />, containerEl );
} );

addAction(
	'ithemes-security.admin-notices.triggerAction',
	'ithemes-security/admin-notices/solid-welcome',
	function( _, noticeId, actionId ) {
		if ( noticeId !== 'welcome-solidwp' || actionId !== 'open' ) {
			return;
		}

		const container = document.createElement( 'div' );
		container.classList.add( 'solid-welcome-container' );
		document.body.appendChild( container );

		const onClose = () => {
			container.remove();
		};

		render( <SolidWelcome onClose={ onClose } />, container );
	}
);
