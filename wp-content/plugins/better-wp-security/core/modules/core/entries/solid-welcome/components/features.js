/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { createInterpolateElement } from '@wordpress/element';

/**
 * SolidWP dependencies
 */
import {
	Text,
	TextSize,
	TextVariant,
	TextWeight,
} from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { coreStore } from '@ithemes/security.packages.data';
import { WelcomeFlowBanner } from './index';
import UpdatedUI from './images/updated-ui.png';
import Firewall from './images/firewall.png';
import SiteScan from './images/site-scan.png';
import UserSecurity from './images/user-security.png';
import {
	StyledCard,
	StyledFeature,
	StyledFeaturesContainer,
	StyledThumbnailContainer,
	StyledThumbnail,
	StyledFeatureTextContainer,
	StyledUpgradeText,
} from '../styles';

function NewFeature( { heading, description, thumbnail } ) {
	return (
		<StyledFeature>
			<StyledThumbnailContainer>
				{ thumbnail }
			</StyledThumbnailContainer>
			<StyledFeatureTextContainer>
				<Text
					weight={ TextWeight.HEAVY }
					size={ TextSize.LARGE }
					text={ heading }
				/>
				<Text variant={ TextVariant.MUTED } text={ description } />
			</StyledFeatureTextContainer>
		</StyledFeature>
	);
}

export function NewFeatures( { features } ) {
	return (
		<StyledFeaturesContainer>
			{ features.map( ( feature ) => (
				<NewFeature
					key={ feature.heading }
					thumbnail={ feature.thumbnail }
					heading={ feature.heading }
					description={ feature.description } />
			) ) }
		</StyledFeaturesContainer>
	);
}

export function CardTwo( { installType } ) {
	return (
		<StyledCard>
			<WelcomeFlowBanner text={ __( 'Take advantage of these new features', 'better-wp-security' ) } />
			<NewFeatures features={ [
				{
					heading: __( 'Clear, Concise User Interface', 'better-wp-security' ),
					description: __( 'Quickly view critical data including current bans, lockouts, threats blocked, site scan results, user security profiles, and vulnerable software.', 'better-wp-security' ),
					thumbnail: <StyledThumbnail src={ UpdatedUI } alt="" />,
				},
			] } />

			{ installType === 'free' ? (
				<>
					<NewFeatures features={ [ {
						heading: __( 'Clear Visibility Into Firewall Protection ', 'better-wp-security' ),
						description: __( 'Gain instant insight into the attacks prevented by your firewall with a new, easy-to-read screen. ', 'better-wp-security' ),
						thumbnail: <StyledThumbnail src={ Firewall } alt="" />,
					} ] } />
					<StyledUpgradeText
						align="center"
						variant={ TextVariant.MUTED }
						text={ createInterpolateElement(
							__( '<a>Upgrade to Solid Security Pro</a> for Patchstack integration to fix vulnerabilities and prevent attacks when your attention is elsewhere. ', 'better-wp-security' ),
							{
							// eslint-disable-next-line jsx-a11y/anchor-has-content
								a: <a href="https://go.solidwp.com/welcome-upgrade-security-pro-patchstack" />,
							}
						) }
					/>
				</>
			) : (
				<NewFeatures features={ [ {
					heading: __( 'Powerful Firewall Capabilities', 'better-wp-security' ),
					description: __( 'Solid Security can automatically add block rules when it identifies suspicious behavior, but you may add your own rules too.', 'better-wp-security' ),
					thumbnail: <StyledThumbnail src={ Firewall } alt="" />,
				} ] } />
			) }
		</StyledCard>
	);
}

export function CardThree( { installType } ) {
	const { hasPatchstack } = useSelect( ( select ) => ( {
		hasPatchstack: select( coreStore ).hasPatchstack(),
	} ), [] );

	return (
		<StyledCard>
			<WelcomeFlowBanner text={ __( 'Features continued…', 'better-wp-security' ) } />
			<NewFeatures features={ [
				{
					heading: __( 'New Site Scan Panel ', 'better-wp-security' ),
					description: __( 'View all risks in one place. Any trouble surfacing in Google Safe Browsing, 2FA logins, or weak passwords are posted here.', 'better-wp-security' ),
					thumbnail: <StyledThumbnail src={ SiteScan } alt="" />,
				},
				{
					heading: __( 'User Security Panel ', 'better-wp-security' ),
					description: __( 'Quickly apply and manage a uniform security policy for all your users.', 'better-wp-security' ),
					thumbnail: <StyledThumbnail src={ UserSecurity } alt="" />,
				},
			] } />

			{ installType === 'free' && (
				<StyledUpgradeText
					align="center"
					variant={ TextVariant.MUTED }
					text={ createInterpolateElement(
						__( 'Want more powerful features? <a>Check out Solid Suite</a>. ', 'better-wp-security' ),
						{
						// eslint-disable-next-line jsx-a11y/anchor-has-content
							a: <a href="https://go.solidwp.com/check-out-solid-suite" />,
						}
					) }
				/>
			) }

			{ installType === 'pro' && ! hasPatchstack && (
				<StyledUpgradeText
					align="center"
					variant={ TextVariant.MUTED }
					text={ createInterpolateElement(
						__( 'Want to reduce your WordPress site’s risk to nearly zero? <a>Upgrade to Solid Security Pro with Patchstack</a>.', 'better-wp-security' ),
						{
							// eslint-disable-next-line jsx-a11y/anchor-has-content
							a: <a href="https://go.solidwp.com/upgrade-virtual-patching" />,
						}
					) }
				/>
			) }
		</StyledCard>
	);
}
