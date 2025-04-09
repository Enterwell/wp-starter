/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';

/**
 * SolidWP dependencies
 */
import {
	Button,
	TextSize,
	TextVariant,
	TextWeight,
} from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { Styled2FAContainer, StyledHeading, StyledTextContainer } from './styles';

export default function App( { twoFactorOnboard, user } ) {
	const buttonText = user.solid_2fa === 'not-enabled' ? __( 'Enable', 'better-wp-security' ) : __( 'Configure', 'better-wp-security' );
	return (
		<Styled2FAContainer>
			<StyledHeading
				level={ 3 }
				size={ TextSize.LARGE }
				variant={ TextVariant.DARK }
				weight={ TextWeight.HEAVY }
				text={ __( 'Two-Factor Authentication', 'better-wp-security' ) }
			/>
			<StyledTextContainer>
				<p>
					{ createInterpolateElement(
						__( 'Enabling two-factor authentication greatly increases the security of your user account on this site. With two-factor authentication enabled, after you submit your username and password, you will be asked for an additional authentication code to complete your login. <b>Two-factor authentication codes can come from an app that runs on your mobile device, an email that is sent to you after you log in with your username and password, or from a pre-generated list of codes.</b> The button below allows you to configure which of these authentication code providers are enabled for your user.', 'better-wp-security' ),
						{ b: <strong /> }
					) }
				</p>
			</StyledTextContainer>
			<Button
				href={ twoFactorOnboard }
				variant="primary"
				text={ buttonText }
			/>
		</Styled2FAContainer>
	);
}
