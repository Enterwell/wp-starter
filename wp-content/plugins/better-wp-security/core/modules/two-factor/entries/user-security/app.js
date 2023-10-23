/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { FiltersGroupDropdown } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import {
	EditingModalActionFill,
	EditingModalActionButton,
	UserSecurityFilterFill,
} from '@ithemes/security.pages.user-security';
import './style.scss';

export default function App() {
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
					slug="two_factor"
					title={ __( 'Two Factor Authentication', 'better-wp-security' ) }
					options={ [
						{ value: 'enabled', label: __( 'Has Enabled', 'better-wp-security' ) },
						{ value: 'disabled', label: __( 'Does Not Have Enabled', 'better-wp-security' ) },
					] }
				/>
			</UserSecurityFilterFill>
		</>
	);
}
