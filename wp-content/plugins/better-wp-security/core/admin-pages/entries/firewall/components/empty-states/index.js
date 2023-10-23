/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';

/**
 * Solid dependencies
 */
import { Button, Text, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { useGlobalNavigationUrl } from '@ithemes/security-utils';
import { FirewallBasic, FirewallNoRules, VulnerabilitySuccess } from '@ithemes/security-style-guide';
import { HiResIcon } from '@ithemes/security-ui';
import { StyledEmptyState, StyledContent } from './styles';

export function EmptyStateBasic() {
	return (
		<StyledEmptyState>
			<StyledContent>
				<HiResIcon icon={ <FirewallBasic /> } />
				<Text
					variant={ TextVariant.DARK }
					weight={ 700 }
					text={ __( 'The ability to add custom firewall rules is coming soon!', 'better-wp-security' ) }
				/>
				<Text
					align="center"
					variant={ TextVariant.DARK }
					text={
						createInterpolateElement(
							__( 'Stay updated on our latest improvements on the <a>SolidWP Blog</a>.', 'better-wp-security' ),
							{
								// eslint-disable-next-line jsx-a11y/anchor-has-content
								a: <a href="https://go.solidwp.com/firewall-solidwp-blog" /> }
						)

					}
				/>
			</StyledContent>
		</StyledEmptyState>
	);
}

export function EmptyStateProHasVulnerabilities() {
	const vulnerabilitiesUrl = useGlobalNavigationUrl( 'vulnerabilities' );
	return (
		<StyledEmptyState>
			<StyledContent>
				<HiResIcon icon={ <FirewallNoRules /> } />
				<Text
					align="center"
					variant={ TextVariant.DARK }
					weight={ 700 }
					text={ __( 'Your site has vulnerable software installed, but there are no firewall rules available.', 'better-wp-security' ) }
				/>
				<Text
					align="center"
					variant={ TextVariant.DARK }
					text={ __( 'Visit the vulnerabilities page to learn how to keep your site safe.', 'better-wp-security' ) }
				/>
				<Button
					href={ vulnerabilitiesUrl }
					variant="primary"
					text={ __( 'View Vulnerabilities', 'better-wp-security' ) }
				/>
			</StyledContent>
		</StyledEmptyState>
	);
}

export function EmptyStatePro() {
	return (
		<StyledEmptyState>
			<StyledContent>
				<HiResIcon icon={ <VulnerabilitySuccess /> } />
				<Text
					align="center"
					variant={ TextVariant.DARK }
					weight={ 700 }
					text={ __( 'No firewall rules are active on your site because you have no vulnerable software installed.', 'better-wp-security' ) }
				/>
				<Text
					align="center"
					variant={ TextVariant.DARK }
					text={ __( 'Keep up the good work!', 'better-wp-security' ) }
				/>
			</StyledContent>
		</StyledEmptyState>
	);
}
