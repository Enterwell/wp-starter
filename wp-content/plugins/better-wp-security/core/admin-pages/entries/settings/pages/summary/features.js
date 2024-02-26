/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { key } from '@wordpress/icons';
import { createInterpolateElement } from '@wordpress/element';

/**
 * SolidWP dependencies
 */
import {
	Heading,
	Text,
	TextVariant,
	TextSize,
	TextWeight,
	ListItem,
} from '@ithemes/ui';

/**
 * Internal dependencies
 */
import {
	StyledFeatures,
	StyledFeaturesLayout,
	StyledFeaturesList,
	StyledUpgradeButton,
} from './styles';

const basicFeatures = [
	createInterpolateElement(
		__( 'A <b>Firewall</b> that automatically blocks brute force logins, password-stuffing, DDoS attacks, bad bots, and any attempts to exploit vulnerable software on your site.', 'better-wp-security' ),
		{ b: <Text weight={ 700 } /> }
	),
	createInterpolateElement(
		__( '<b>Virtual Patches</b> from <b>Patchstack</b> that protect insecure themes and plugins even before vulnerabilities are publicly disclosed and an official patch is released. You may not be able to apply security updates immediately, but with Solid Security Pro you’re still protected until you’re ready to update.', 'better-wp-security' ),
		{ b: <Text weight={ 700 } /> }
	),
	createInterpolateElement(
		__( '<b>Version Management</b> gives you precise control over when to apply or delay an automatic update to any plugin, theme, extension, or WordPress core.', 'better-wp-security' ),
		{ b: <Text weight={ 700 } /> }
	),
	createInterpolateElement(
		__( '<b>Passkeys</b> and other <b>Passwordless</b> Login options mitigate the risks associated with users who often share, reuse, and choose weak passwords. Increase your users’ security and simplify their login experience.', 'better-wp-security' ),
		{ b: <Text weight={ 700 } /> }
	),
	createInterpolateElement(
		__( 'Multiple <b>CAPTCHA</b> options, including <b>Cloudflare Turnstile</b>, eliminate bot traffic from your login screens and other forms. You won’t be asked to solve a puzzle or identify objects to prove you’re a human — unless you’re a bot.', 'better-wp-security' ),
		{ b: <Text weight={ 700 } /> }
	),
];

const proFeatures = [
	__( 'Passwordless Login and Passkeys', 'better-wp-security' ),
	__( 'Two-Factor', 'better-wp-security' ),
	__( 'Captcha', 'better-wp-security' ),
	__( 'Version Management', 'better-wp-security' ),
	__( 'Trusted Devices', 'better-wp-security' ),
	__( 'Import & Export', 'better-wp-security' ),
	__( 'WP-CLI', 'better-wp-security' ),
];

export default function Features( { installType } ) {
	return (
		<StyledFeatures>
			<StyledFeaturesLayout direction="column" justify="start" expanded={ false } gap={ 4 }>
				<Heading
					level={ 2 }
					weight={ TextWeight.NORMAL }
					size={ TextSize.EXTRA_LARGE }
					variant={ TextVariant.ACCENT }
					text={ installType === 'free'
						? __( 'Reduce your site’s security risk to nearly zero with Solid Security Pro.', 'better-wp-security' )
						: __( 'Greatly enhance your website’s security with Solid Security Pro.', 'better-wp-security' ) }
				/>
				<Text
					variant={ TextVariant.MUTED }
					text={ installType === 'free'
						? __( 'Extended features for professionals include:', 'better-wp-security' )
						: __( 'You are a WordPress security superstar! Be sure to take advantage of the features that comes with your paid plan. Paid features include…', 'better-wp-security' ) }
				/>
				<StyledFeaturesList textVariant={ TextVariant.DARK } gap={ 0 }>
					{ installType === 'free'
						? <>
							{ basicFeatures.map( ( feature, i ) => (
								<ListItem icon={ key } text={ feature } key={ i } />
							) ) }
						</>
						: <>
							{ proFeatures.map( ( feature, i ) => (
								<ListItem icon={ key } text={ feature } key={ i } />
							) ) }
						</>
					}
				</StyledFeaturesList>
				{ installType === 'free'
					? (
						<StyledUpgradeButton
							href="https://go.solidwp.com/go-pro-onboarding"
							variant="primary"
							text={ __( 'Upgrade to Solid Security Pro', 'better-wp-security' ) }
						/>
					)
					: <Text variant={ TextVariant.MUTED } text={ __( 'and more!', 'better-wp-security' ) } /> }
			</StyledFeaturesLayout>
		</StyledFeatures>
	);
}
