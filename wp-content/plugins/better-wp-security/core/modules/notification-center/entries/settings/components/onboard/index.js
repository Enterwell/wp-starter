/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Solid dependencies
 */
import { TextSize, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { OnboardHeader, useNavigation } from '@ithemes/security.pages.settings';
import { SettingsForm } from '../';
import {
	StyledOnboard,
	StyledEncouragementText,
	StyledCompleteButton,
} from './styles';
import { useViewportMatch } from '@wordpress/compose';

export default function Onboard( { usersAndRoles, apiError } ) {
	const { goNext } = useNavigation();
	const isMedium = useViewportMatch( 'medium' );

	return (
		<StyledOnboard>
			{ isMedium && (
				<StyledEncouragementText
					as="p"
					align="center"
					size={ TextSize.EXTRA_LARGE }
					variant={ TextVariant.MUTED }
					text={ __( 'You’re almost done, just one last topic to cover!', 'better-wp-security' ) }
				/>
			) }
			<OnboardHeader
				title={ __( 'Notifications', 'better-wp-security' ) }
				description={ __(
					'Manage and configure email notifications sent by Solid Security related to various features.',
					'better-wp-security'
				) }
				showIndicator
			/>
			<SettingsForm usersAndRoles={ usersAndRoles } apiError={ apiError } />

			<StyledCompleteButton
				variant="primary"
				onClick={ goNext }
				text={ __( 'Complete Setup', 'better-wp-security' ) }
			/>
		</StyledOnboard>
	);
}
