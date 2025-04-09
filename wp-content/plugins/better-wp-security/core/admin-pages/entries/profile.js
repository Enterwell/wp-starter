/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import { setLocaleData } from '@wordpress/i18n';
import { getPlugins } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import App, { UserProfileFill } from './profile/app.js';
export { App, UserProfileFill };

// Silence warnings until JS i18n is stable.
setLocaleData( { '': {} }, 'ithemes-security-pro' );

domReady( () => {
	const el = document.getElementById( 'itsec-profile-root' );
	const plugins = getPlugins( 'solid-security-user-profile' );

	if ( el ) {
		render(
			<App
				plugins={ plugins }
				canManage={ el.dataset.canManage === '1' }
				userId={ Number.parseInt( el.dataset.user, 10 ) }
			/>,
			document.getElementById( 'itsec-profile-root' )
		);
	}
} );
