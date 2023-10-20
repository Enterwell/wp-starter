/**
 * External dependencies
 */
import { ThemeProvider, useTheme } from '@emotion/react';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useMemo } from '@wordpress/element';
import { close as dismissIcon } from '@wordpress/icons';

/**
 * SolidWP dependencies
 */
import { Text, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import {
	BelowToolbarFill,
	EditCardsFill,
} from '@ithemes/security.dashboard.api';
import {
	useConfigContext,
	PromoCard,
} from '@ithemes/security.dashboard.dashboard';
import { RebrandingLogos } from '@ithemes/security-style-guide';
import { FlexSpacer } from '@ithemes/security-components';
import { useLocalStorage } from '@ithemes/security-hocs';
import {
	StyledBanner,
	StyledBannerButton,
	StyledBannerHeading,
	StyledTextContainer,
	StyledStellarSaleDismiss,
	StyledBFCMBanner,
	StyledBFCMTextContainer,
	StyledBFCMHeading,
	StyledBFCMText,
	StyledBFCMButton,
	StyledLogo,
	StyledBFCMDismiss,
} from './styles';

export default function App() {
	const { installType } = useConfigContext();

	return (
		<>
			<BelowToolbarFill>
				{ ( { page, dashboardId } ) =>
					dashboardId > 0 && page === 'view-dashboard' && (
						<>
							<SolidSecurityDashboardBanner installType={ installType } />
							<SolidSecurityBFCM2023Banner installType={ installType } />
						</>
					)
				}
			</BelowToolbarFill>
			{ installType === 'free' && (
				<EditCardsFill>
					<PromoCard title={ __( 'Trusted Devices', 'better-wp-security' ) } />
					<PromoCard title={ __( 'Updates Summary', 'better-wp-security' ) } />
					<PromoCard title={ __( 'User Security Profiles', 'better-wp-security' ) } />
				</EditCardsFill>
			) }
		</>
	);
}

const start = Date.UTC( 2023, 6, 24, 8, 0, 0 );
const end = Date.UTC( 2024, 1, 1, 8, 0, 0 );
const now = Date.now();

function SolidSecurityDashboardBanner( { installType } ) {
	const [ isDismissed, setIsDismissed ] = useLocalStorage( 'itsecIsSolid' );
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

	if ( start > now || end < now ) {
		return null;
	}

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
						text={ __( 'iThemes is now SolidWP', 'better-wp-security' ) }
					/>
					<Text
						size="subtitleSmall"
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
					href={ installType === 'free'
						? 'https://go.solidwp.com/dashboard-free-ithemes-is-now-solidwp'
						: 'https://go.solidwp.com/dashboard-ithemes-is-now-solidwp'
					}
					weight={ 600 }
				>
					{ __( 'Learn more', 'better-wp-security' ) }
				</StyledBannerButton>

			</StyledBanner>
		</ThemeProvider>
	);
}

// November 20, 2023 UTC
const saleStart = Date.UTC( 2023, 10, 20, 0, 0, 0 );
// December 3, 2023 (inclusive of all US timezones)
const saleEnd = Date.UTC( 2023, 11, 4, 9, 59, 59 );

function SolidSecurityBFCM2023Banner( { installType } ) {
	const [ isDismissed, setIsDismissed ] = useLocalStorage( 'solidSecurityBFCM2023' );
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

	if ( saleStart > now || saleEnd < now ) {
		return null;
	}

	if ( isDismissed ) {
		return null;
	}

	return (
		<ThemeProvider theme={ theme }>
			<StyledBFCMBanner>
				<StyledBFCMTextContainer>
					<StyledBFCMHeading
						level={ 2 }
						variant={ TextVariant.WHITE }
						weight={ TextWeight.HEAVY }
						text={ __( 'Save 40% on SolidWP', 'better-wp-security' ) }
					/>
					<StyledBFCMText
						variant={ TextVariant.WHITE }
						text={ __( 'Purchase new products during the Black Friday Sale.' ) }
					/>
					<StyledBFCMButton
						href={ installType === 'free' ? 'https://go.solidwp.com/bfcm-go-pro' : 'https://go.solidwp.com/bfcm-security-pro-solid-suite' }
						weight={ 500 }
					>
						{ __( 'Get Solid Suite', 'better-wp-security' ) }
					</StyledBFCMButton>
				</StyledBFCMTextContainer>
				<StyledBFCMDismiss
					label={ __( 'Dismiss', 'better-wp-security' ) }
					icon={ dismissIcon }
					onClick={ () => setIsDismissed( true ) }
				/>
				<StyledLogo />
			</StyledBFCMBanner>
		</ThemeProvider>
	);
}
