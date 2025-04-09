/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';
import { setLocaleData } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';

// Silence warnings until JS i18n is stable.
setLocaleData( { '': {} }, 'ithemes-security-pro' );

/**
 * Internal dependencies
 */
import { useSingletonEffect } from '@ithemes/security-hocs';
import * as bannedUsers from './dashboard/cards/banned-users';
import * as activeLockouts from './dashboard/cards/active-lockouts';

registerPlugin( 'itsec-core-dashboard', {
	render() {
		return <App />;
	},
} );

function App() {
	const { registerCard } = useDispatch( 'ithemes-security/dashboard' );

	useSingletonEffect( App, () =>
		[ bannedUsers, activeLockouts ].forEach( ( { slug, settings } ) =>
			registerCard( slug, settings )
		)
	);

	return null;
}
