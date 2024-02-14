/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';
import { setLocaleData, __ } from '@wordpress/i18n';

// Silence warnings until JS i18n is stable.
setLocaleData( { '': {} }, 'ithemes-security-pro' );

/**
 * Internal dependencies
 */
import App from './profile/app.js';
import { UserProfileFill } from '@ithemes/security.pages.profile';

export function initialize( { twoFactorOnboard } ) {
	registerPlugin( 'itsec-two-factor-profile', {
		render() {
			return (
				<UserProfileFill>
					{ ( { name, userId, user } ) => (
						name === 'itsec-two-factor-profile' && (
							<App userId={ userId } twoFactorOnboard={ twoFactorOnboard } user={ user } />
						)
					) }
				</UserProfileFill>
			);
		},
		order: 3,
		label: __( 'Two-Factor Authentication', 'better-wp-security' ),
		scope: 'solid-security-user-profile',
		isAvailable: ( user, currentUserId ) => {
			return user.solid_2fa !== 'not-available' && user.solid_2fa !== null && currentUserId === user.id;
		},
	} );
}
