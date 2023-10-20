/**
 * External dependencies
 */
import { ThemeProvider, useTheme } from '@emotion/react';

/**
 * WordPress dependencies
 */
import { closeSmall as dismissIcon } from '@wordpress/icons';
import { useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Text } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { FlexSpacer } from '@ithemes/security-components';
import { useLocalStorage } from '@ithemes/security-hocs';
import { RebrandingLogos } from '@ithemes/security-style-guide';
import { useConfigContext } from '../../utils';
import {
	StyledBanner,
	StyledBannerButton,
	StyledBannerHeading,
	StyledTextContainer,
	StyledStellarSaleDismiss,
} from './styles';

export default function SolidSecuritySettingsBanner() {
	const [ isDismissed, setIsDismissed ] = useLocalStorage( 'itsecIsSolid' );
	const { installType } = useConfigContext();
	const baseTheme = useTheme();
	const theme = useMemo( () => ( {
		...baseTheme,
		colors: {
			...baseTheme.colors,
			text: {
				...baseTheme.colors.text,
				white: '#F9FAF9',
			},
		},
	} ), [ baseTheme ] );

	if ( isDismissed ) {
		return null;
	}

	return (
		<ThemeProvider theme={ theme }>
			<StyledBanner>
				<RebrandingLogos />
				<StyledTextContainer>
					<StyledBannerHeading
						level={ 2 }
						weight={ 700 }
						variant="dark"
						size="extraLarge"
						text={ __( 'iThemes Security is now Solid Security', 'better-wp-security' ) }
					/>
					<Text
						size="subtitleSmall"
						weight={ 500 }
						variant="dark"
						text={ __( 'We have been working hard for almost a year to bring you incredible new features in the form of our new and improved brand: SolidWP. Discover what’s new!', 'better-wp-security' ) }
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
					weight={ 600 }>
					{ __( 'Learn more', 'better-wp-security' ) }
				</StyledBannerButton>
			</StyledBanner>
		</ThemeProvider>
	);
}
