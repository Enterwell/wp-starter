/**
 * WordPress dependencies
 */
import { Flex } from '@wordpress/components';
import { __, _n, sprintf } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Button, Heading, Text, TextSize, TextVariant, TextWeight } from '@ithemes/ui';
import { Patchstack } from '@ithemes/security-style-guide';

/**
 * Internal dependencies
 */
import { SiteScanIcon, SoftwareVulnerabilityCard } from '../../../../../components';
import { useConfigContext, useHighlightedVulnerabilities } from '../../../../../utils';
import EnableScheduling from './enable-scheduling';
import {
	StyledCardGraphic,
	StyledPoweredBy,
	StyledResultsCard,
	StyledResultsPage,
} from './styles';
import vulnPreview from './vuln-preview.png';

export default function FoundIssues( { issues, onAnswer } ) {
	const { show, remaining } = useHighlightedVulnerabilities( issues, 2 );
	const { installType } = useConfigContext();

	return (
		<StyledResultsPage>
			<StyledResultsCard>
				<Flex gap={ 4 } justify="flex-start">
					<SiteScanIcon found />
					<Flex direction="column" gap={ 3 }>
						<Heading level={ 3 } text={ __( 'We found some issues', 'better-wp-security' ) } size={ TextSize.HUGE } weight={ TextWeight.NORMAL } />
						<Text
							text={ __( 'Exploiting vulnerable software is one of the main tactics used by bad actors to hack your site.', 'better-wp-security' ) }
							variant={ TextVariant.MUTED }
							weight={ TextWeight.HEAVY }
						/>
					</Flex>
				</Flex>

				{ show.map( ( item ) => (
					<SoftwareVulnerabilityCard key={ item.software.slug + item.software.type.slug } { ...item } />
				) ) }

				{ remaining > 0 && (
					<Text
						/* translators: 1. Count of vulnerabilities. */
						text={ sprintf( _n( 'We also found %d additional vulnerability on your site.', 'We also found %d additional vulnerabilities on your site.', remaining, 'better-wp-security' ), remaining ) }
						variant={ TextVariant.MUTED }
					/>
				) }

				<Flex direction="column" gap={ 3 }>
					<Text
						text={
							installType === 'free'
								? __( 'Pro Tip: Solid Security can scan your site twice a day to ensure it stays free of any vulnerabilities, just enable the feature below.', 'better-wp-security' )
								: __( 'Pro Tip: Solid Security will scan your site twice a day to ensure it stays free of any vulnerabilities.', 'better-wp-security' )
						}
						variant={ TextVariant.MUTED }
						weight={ TextWeight.HEAVY }
					/>
					{ installType === 'free' && (
						<EnableScheduling />
					) }
				</Flex>
			</StyledResultsCard>

			<Flex direction="column" gap={ 3 }>
				<Heading level={ 4 } text={ __( 'Here’s what’s next', 'better-wp-security' ) } size={ TextSize.EXTRA_LARGE } weight={ TextWeight.NORMAL } />
				<Text
					text={ __( 'We will revisit and resolve your existing vulnerabilities once you’ve set up Solid Security.', 'better-wp-security' ) }
					variant={ TextVariant.MUTED }
					weight={ TextWeight.HEAVY }
				/>
			</Flex>

			<StyledCardGraphic position="right">
				<img src={ vulnPreview } alt={ __( 'Preview of the Vulnerabilities page in Solid Security.', 'better-wp-security' ) } width={ 345 } height={ 120 } />
			</StyledCardGraphic>

			<Flex direction="column" gap={ 2 }>
				<Heading level={ 5 } text={ __( 'Why not resolve the vulnerabilities now?', 'better-wp-security' ) } size={ TextSize.SUBTITLE_SMALL } />
				<Text as="p" text={ __( 'Manually resolving vulnerabilities requires some poking around. It’s best to have a configured security environment before jumping into manually resolving vulnerabilities.', 'better-wp-security' ) } />
			</Flex>

			<Button text={ __( 'Continue Setup', 'better-wp-security' ) } variant="primary" onClick={ () => onAnswer( issues.length ) } />

			<StyledPoweredBy>
				<Text text={ __( 'Powered by', 'better-wp-security' ) } variant={ TextVariant.MUTED } size={ TextSize.SMALL } />
				<Patchstack width={ 171 } alt={ __( 'Patchstack', 'better-wp-security' ) } />
			</StyledPoweredBy>
		</StyledResultsPage>
	);
}
