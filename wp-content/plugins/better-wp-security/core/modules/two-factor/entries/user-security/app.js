/**
 * External dependencies
 */
import { identity } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * SolidWP dependencies
 */
import { FiltersGroupDropdown } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import {
	EditingModalActionFill,
	EditingModalActionButton,
	UserSecurityFilterFill,
} from '@ithemes/security.pages.user-security';
import './style.scss';

export default function App() {
	const { protectUserGroup } = useSelect( ( select ) => ( {
		protectUserGroup: select( MODULES_STORE_NAME ).getSetting( 'two-factor', 'protect_user_group' ),
	} ), [] );

	return (
		<>
			<EditingModalActionFill>
				<EditingModalActionButton
					title={ __( 'Remind Users to Set Up Two-Factor Authentication', 'better-wp-security' ) }
					description={ __( 'Send a reminder by email to prompt users to set up Two-Factor Authentication for increased login security.', 'better-wp-security' ) }
					buttonText={ __( 'Send a Two-Factor Reminder Email', 'better-wp-security' ) }
					slug="send-2fa-reminder"
					confirmationText={ __( 'Sending Two-Factor Reminder', 'better-wp-security' ) }
				/>
			</EditingModalActionFill>
			<UserSecurityFilterFill>
				<FiltersGroupDropdown
					slug="solid_2fa"
					title={ __( 'Two Factor Authentication', 'better-wp-security' ) }
					options={ [
						{ value: 'enabled', label: __( 'Has Enabled', 'better-wp-security' ), summary: __( '2FA Enabled', 'better-wp-security' ) },
						protectUserGroup?.length > 0 && { value: 'enforced-not-configured', label: __( 'Enforced, Not Configured', 'better-wp-security' ), summary: __( '2FA Enforced', 'better-wp-security' ) },
						{ value: 'not-enabled', label: __( 'Does Not Have Enabled', 'better-wp-security' ), summary: __( '2FA Not Enabled', 'better-wp-security' ) },
					].filter( identity ) }
				/>
			</UserSecurityFilterFill>
		</>
	);
}
