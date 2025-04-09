/**
 * WordPress dependencies
 */
import { Flex } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Button, Heading, Text, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { StyledCardGraphic, StyledResultsCard, StyledResultsPage } from './styles';
import { useConfigContext } from '../../../../../utils';
import { SiteScanIcon } from '../../../../../components/';
import Tools from './tools';
import EnableScheduling from './enable-scheduling';

export default function NoIssues( { onAnswer } ) {
	const { installType } = useConfigContext();

	return (
		<StyledResultsPage isWide>
			<StyledResultsCard>
				<Flex gap={ 4 } justify="flex-start">
					<SiteScanIcon found={ false } />
					<Flex direction="column" gap={ 3 }>
						<Heading level={ 3 } text={ __( 'Congrats, we didn’t find any vulnerabilities!', 'better-wp-security' ) } size={ TextSize.HUGE } weight={ TextWeight.NORMAL } />
						<Text
							text={
								installType === 'free'
									? __( 'Pro Tip: Solid Security can scan your site twice a day to ensure it stays free of any vulnerabilities, just enable the feature below.', 'better-wp-security' )
									: __( 'Pro Tip: Solid Security will scan your site twice a day to ensure it stays free of any vulnerabilities.', 'better-wp-security' )
							}
							variant={ TextVariant.MUTED }
							weight={ TextWeight.HEAVY }
						/>
					</Flex>
				</Flex>
				{ installType === 'free' && (
					<EnableScheduling />
				) }
			</StyledResultsCard>

			<Flex direction="column" gap={ 3 }>
				<Heading level={ 4 } text={ __( 'Build on your success by setting up Solid Security now', 'better-wp-security' ) } size={ TextSize.EXTRA_LARGE } weight={ TextWeight.NORMAL } />
				<Text
					text={ __( 'Great job on keeping all your plugins & themes up to date. Let’s help you take the next step.', 'better-wp-security' ) }
					variant={ TextVariant.MUTED }
					weight={ TextWeight.HEAVY }
				/>
			</Flex>

			<StyledCardGraphic position="right">
				<Tools />
			</StyledCardGraphic>

			<Flex direction="column" gap={ 2 }>
				<Heading level={ 5 } text={ __( 'What’s Next?', 'better-wp-security' ) } size={ TextSize.SUBTITLE_SMALL } />
				<Text as="p" text={ __( 'We guide you through our main security features to get you started on the right foot. We cover features related to security topics like Two-Factor Authentication, Password Policy, Firewall features, and more.', 'better-wp-security' ) } />
			</Flex>

			<Button text={ __( 'Continue Setup', 'better-wp-security' ) } variant="primary" onClick={ () => onAnswer( 0 ) } />
		</StyledResultsPage>
	);
}

