/**
 * External dependencies
 */
import { ThemeProvider, useTheme } from '@emotion/react';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { useMemo } from '@wordpress/element';
import { closeSmall as dismissIcon } from '@wordpress/icons';

/**
 * iThemes dependencies
 */
import { Text } from '@ithemes/ui';

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
import { LogoProWhite, SecurityFreeLogo } from '@ithemes/security-style-guide';
import { FlexSpacer } from '@ithemes/security-components';
import { useLocalStorage } from '@ithemes/security-hocs';
import {
	StyledBanner,
	StyledBannerButton,
	StyledBannerHeading,
	StyledLogoContainer,
	StyledTextContainer,
	StyledSolidLogo,
	StyledStellarSaleDismiss,
} from './styles';
import './style.scss';

export default function App() {
	const { installType } = useConfigContext();

	return (
		<>
			<BelowToolbarFill>
				{ ( { page, dashboardId } ) =>
					dashboardId > 0 && page === 'view-dashboard' && (
						<>
							<SolidSecurityDashboardBanner installType={ installType } />
							{ installType === 'free' && (
								<Footer />
							) }

						</>
					)
				}
			</BelowToolbarFill>
			<EditCardsFill>
				<PromoCard title={ __( 'Trusted Devices', 'better-wp-security' ) } />
				<PromoCard title={ __( 'Updates Summary', 'better-wp-security' ) } />
				<PromoCard title={ __( 'User Security Profiles', 'better-wp-security' ) } />
			</EditCardsFill>
		</>
	);
}

function Footer() {
	const [ isDismissed, setIsDismiss ] = useLocalStorage(
		'itsecPromoProUpgrade'
	);

	if ( isDismissed ) {
		return null;
	}

	return (
		<aside className="itsec-promo-pro-upgrade">
			<LogoProWhite />
			<section>
				<h2>
					{ __( 'Unlock More Security Features with Pro', 'better-wp-security' ) }
				</h2>
				<p>
					{ __(
						'Go beyond the basics with premium features & support.',
						'better-wp-security'
					) }
				</p>
			</section>
			<FlexSpacer />
			<a
				href="https://ithem.es/included-with-pro"
				className="itsec-promo-pro-upgrade__details"
			>
				{ __( 'What’s included with Pro?', 'better-wp-security' ) }
			</a>
			<Button
				className="itsec-promo-pro-upgrade__button"
				href="https://ithem.es/go-security-pro-now"
			>
				{ __( 'Go Pro Now', 'better-wp-security' ) }
			</Button>
			<Button
				icon="dismiss"
				className="itsec-promo-pro-upgrade__close"
				label={ __( 'Dismiss', 'better-wp-security' ) }
				onClick={ () => setIsDismiss( true ) }
			/>
		</aside>
	);
}

function SolidSecurityDashboardBanner( { installType } ) {
	const [ isDismissed, setIsDismissed ] = useLocalStorage( 'itsecBecomingSolid' );
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
					href={ installType === 'free'
						? 'https://go.solidwp.com/security-free-dashboard-ithemes-becoming-solidwp'
						: 'https://go.solidwp.com/security-dashboard-ithemes-becoming-solidwp'
					}
					weight={ 600 }
				>
					{ __( 'Learn more', 'better-wp-security' ) }
				</StyledBannerButton>
			</StyledBanner>
		</ThemeProvider>
	);
}
