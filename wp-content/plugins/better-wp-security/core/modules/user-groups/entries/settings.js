/**
 * WordPress dependencies
 */
import { setLocaleData } from '@wordpress/i18n';
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

// Silence warnings until JS i18n is stable.
setLocaleData( { '': {} }, 'ithemes-security-pro' );

/**
 * Internal dependencies
 */
import App from './settings/app.js';

domReady( () => {
	init();

	if ( window.itsecSettingsPage ) {
		window.itsecSettingsPage.events.on( 'modulesReloaded', init );
		window.itsecSettingsPage.events.on( 'moduleReloaded', function( _, module ) {
			if ( 'user-groups' === module ) {
				init();
			}
		} );
	}
} );

function init() {
	const containerEl = document.getElementById( 'itsec-user-groups-settings-root' );
	const noticeEl = document.getElementById( 'itsec-module-messages-container-user-groups' );

	return render(
		<App noticeEl={ noticeEl } />,
		containerEl,
	);
}
