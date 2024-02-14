/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import { getPlugins } from '@wordpress/plugins';

/**
 * Internal dependencies
 */
import { App } from '@ithemes/security.pages.profile';

domReady( () => {
	const el = document.getElementById( 'itsec-core-profile-front-root' );

	if ( el ) {
		const canManage = el.dataset.canManage === '1';
		const plugins = getPlugins( 'solid-security-user-profile' );
		const styleSheet = document.getElementById( 'wp-components-css' );

		if ( styleSheet && styleSheet.parentElement.tagName !== 'HEAD' ) {
			// Move @wordpress/components CSS to the head, so it doesn't
			// have a greater specificity than emotion styles.
			document.head.appendChild( styleSheet );
		}

		const userId = Number.parseInt( el.dataset.user, 10 );
		render( <App userId={ userId } plugins={ plugins } canManage={ canManage } useShadow />, el );
	}
} );
