/**
 * External dependencies
 */
import { ThemeProvider } from '@emotion/react';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { close as dismissIcon } from '@wordpress/icons';

/**
 * iThemes dependencies
 */
import { solidTheme, Text } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { useLocalStorage } from '@ithemes/security-hocs';
import { useConfigContext } from '@ithemes/security.pages.settings';
import { SecurityFreeLogo } from '@ithemes/security-style-guide';
import { FlexSpacer } from '@ithemes/security-components';
import {
	StyledBanner,
	StyledBannerButton,
	StyledBannerHeading,
	StyledLogoContainer,
	StyledSolidLogo,
	StyledStellarSaleDismiss,
	StyledTextContainer,
} from './styles';

export default function BecomingSolid() {
	const [ isDismissed, setIsDismissed ] = useLocalStorage( 'itsecBecomingSolid' );
	const { installType } = useConfigContext();

	if ( isDismissed ) {
		return null;
	}

	return (
		<ThemeProvider theme={ solidTheme }>
			<StyledBanner>
				<StyledLogoContainer>
					<SecurityFreeLogo />
					<StyledSolidLogo />
				</StyledLogoContainer>
				<StyledTextContainer>
					<StyledBannerHeading
						level={ 2 }
						weight={ 700 }
						variant="dark"
						size="extraLarge"
						text={ __( 'iThemes Security is becoming Solid Security', 'better-wp-security' ) }
					/>
					<Text
						size="subtitleSmall"
						weight={ 500 }
						variant="dark"
						text={ __( 'We have been working hard for almost a year to bring you incredible new features in the form of our new and improved brand: SolidWP. Discover what’s coming very soon!', 'better-wp-security' ) }
					/>
				</StyledTextContainer>
				<FlexSpacer />
				<StyledStellarSaleDismiss
					label={ __( 'Dismiss', 'better-wp-security' ) }
					icon={ dismissIcon }
					onClick={ () => setIsDismissed( true ) }
				/>
				<StyledBannerButton
					href={ installType === 'pro'
						? 'https://go.solidwp.com/settings-notification-ithemes-becoming-solidwp'
						: 'https://go.solidwp.com/settings-notification-free-ithemes-becoming-solidwp'
					}
					weight={ 600 }
				>
					{ __( 'Learn more', 'better-wp-security' ) }
				</StyledBannerButton>
			</StyledBanner>
		</ThemeProvider>
	);
}
