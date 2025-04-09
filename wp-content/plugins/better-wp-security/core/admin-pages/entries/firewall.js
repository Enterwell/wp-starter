/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';
import { setLocaleData } from '@wordpress/i18n';

// Silence warnings until JS i18n is stable.
setLocaleData( { '': {} }, 'ithemes-security-pro' );

/**
 * Internal dependencies
 */
import App from './firewall/app.js';
import { createHistory } from './settings/history';

const history = createHistory( document.location, { page: 'itsec-firewall' } );

domReady( () => render( <App history={ history } />, document.getElementById( 'itsec-firewall-root' ) ) );

export {
	BeforeCreateFirewallRuleFill,
	AsideHeaderFill,
	FirewallBannerFill,
} from './firewall/components';
