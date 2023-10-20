/**
 * WordPress dependencies
 */
import { setLocaleData } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { useDispatch } from '@wordpress/data';

// Silence warnings until JS i18n is stable.
setLocaleData( { '': {} }, 'ithemes-security-pro' );

/**
 * Internal dependencies
 */
import { useSingletonEffect } from '@ithemes/security-hocs';
import * as malwareScan from './dashboard/scan';
import * as vulnerableSoftware from './dashboard/vulnerable-software';
import './dashboard/style.scss';

registerPlugin( 'itsec-site-scanner-dashboard', {
	render() {
		return <App />;
	},
} );

function App() {
	const { registerCard } = useDispatch( 'ithemes-security/dashboard' );
	useSingletonEffect( App, () => [ malwareScan, vulnerableSoftware ].forEach( ( { slug, settings } ) => registerCard( slug, settings ) ) );

	return null;
}
