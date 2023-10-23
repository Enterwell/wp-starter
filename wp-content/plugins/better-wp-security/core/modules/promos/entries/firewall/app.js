/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { external, closeSmall as dismissIcon } from '@wordpress/icons';

/**
 * SolidWP dependencies
 */
import { Button, Text, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { AsideHeaderFill, FirewallBannerFill } from '../../../../admin-pages/entries/firewall/app';
import { useLocalStorage } from '@ithemes/security-hocs';
import { coreStore } from '@ithemes/security.packages.data';
import {
	StyledAsideHeader,
	StyledPatchstackBanner,
	StyledTextContainer,
	StyledPatchstackLogo,
	StyledPatchstackButton,
	StyledPatchstackDismiss,
} from './styles';

export default function App() {
	const { installType, hasPatchstack, isLiquidWeb } = useSelect(
		( select ) => ( {
			installType: select( coreStore ).getInstallType(),
			hasPatchstack: select( coreStore ).hasPatchstack(),
			isLiquidWeb: select( coreStore ).isLiquidWebCustomer(),
		} ),
		[]
	);

	return (
		<>
			{ ! hasPatchstack && ! isLiquidWeb && (
				<FirewallBannerFill>
					<PatchstackBanner installType={ installType } />
				</FirewallBannerFill>
			) }

			{ installType === 'free' && (
				<AsideHeaderFill>
					{ installType === 'free' && (
						<StyledAsideHeader>
							<Button
								iconPosition="right"
								text={ __( 'Upgrade', 'better-wp-security' ) }
								variant="secondary"
								href="https://go.solidwp.com/upgrade-virtual-patching"
								target="_blank"
							/>
							<Text
								text={ createInterpolateElement(
									__( 'Upgrade to enable automatic protection with <strong>virtual patches</strong>.', 'better-wp-security' ),
									{
										strong: <strong />,
									}
								) }
								variant={ TextVariant.MUTED }
							/>
						</StyledAsideHeader>
					) }
				</AsideHeaderFill>
			) }
		</>
	);
}

function PatchstackBanner( { installType } ) {
	const [ isDismissed, setIsDismissed ] = useLocalStorage( 'patchstackPromo' );

	if ( isDismissed ) {
		return null;
	}
	return (
		<StyledPatchstackBanner>
			<StyledTextContainer>
				<StyledPatchstackLogo />
				<Text
					text={
						installType === 'free'
							? __( 'Rest easy at night. Upgrade to Solid Security Pro with Patchstack and reduce your WordPress website’s risk to nearly zero thanks to Patchstack’s automated Virtual Patching. Vulnerabilities are patched when your attention is elsewhere and even when a patch hasn’t been released. Go Pro.', 'better-wp-security' )
							: createInterpolateElement(
								__( 'Thank you for being an iThemes Security Pro customer. You recently upgraded to Solid Security Pro and we hope you’re enjoying all the great <a>new features</a>. For even more protection, we suggest enabling Patchstack. This automatically patches vulnerabilities when your attention is elsewhere or before a patch is even released. Purchase an additional license per site.', 'better-wp-security' ), {
									// eslint-disable-next-line jsx-a11y/anchor-has-content
									a: <a href="https://go.solidwp.com/all-the-great-new-features" />,
								}
							) }
					variant={ TextVariant.MUTED }
				/>
			</StyledTextContainer>
			<StyledPatchstackDismiss
				label={ __( 'Dismiss', 'better-wp-security' ) }
				icon={ dismissIcon }
				onClick={ () => setIsDismissed( true ) }
			/>
			<StyledPatchstackButton
				text={ installType === 'free'
					? __( 'Upgrade Now', 'better-wp-security' )
					: __( 'Enable Patchstack', 'better-wp-security' ) }
				icon={ external }
				iconPosition="right"
				href={ installType === 'free'
					? 'https://go.solidwp.com/patchstack-banner-upgrade-now'
					: 'https://go.solidwp.com/enable-patchstack'
				}
			/>
		</StyledPatchstackBanner>
	);
}
