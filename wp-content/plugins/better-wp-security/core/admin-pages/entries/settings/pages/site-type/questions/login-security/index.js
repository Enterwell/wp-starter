/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Flex, Icon } from '@wordpress/components';
import { wordpress as loginIcon, arrowRight as continueIcon } from '@wordpress/icons';

/**
 * SolidWP dependencies
 */
import { Heading, Text, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { ProTag } from '../../../../components';
import { useConfigContext } from '../../../../utils';
import {
	StyledCard,
	StyledGraphicContainer,
	StyledLoginSecurity,
	StyledContinueButton,
	StyledLoginSecurityContent,
	StyledPrimaryContainer,
	StyledIcon,
	StyledFeatureContainer,
	balanceHeading,
	balanceText,
} from './styles';

export default function LoginSecurity( {
	headline,
	reason,
	icon,
	feature,
	onContinue,
	upsell,
	isAnswering,
	renderGraphic,
	children,
} ) {
	const { installType } = useConfigContext();

	return (
		<StyledLoginSecurity>
			<StyledLoginSecurityContent>
				{ renderGraphic && (
					<StyledGraphicContainer inert="true">
						<Icon icon={ loginIcon } size={ 84 } />
						{ renderGraphic() }
					</StyledGraphicContainer>
				) }

				<StyledPrimaryContainer>
					<StyledCard as={ Flex } direction="column" gap={ 3 } expanded={ false }>
						<Heading
							level={ 3 }
							text={ headline }
							size={ TextSize.EXTRA_LARGE }
							variant={ TextVariant.DARK }
							weight={ TextWeight.NORMAL }
							className={ balanceHeading }
						/>
						<Flex direction="column" gap={ 1 } expanded={ false }>
							<Heading
								level={ 4 }
								text={ __( 'Why is this important?', 'better-wp-security' ) }
								size={ TextSize.SUBTITLE_SMALL }
								variant={ TextVariant.DARK }
								weight={ TextWeight.HEAVY }
							/>
							<Text as="p" text={ reason } variant={ TextVariant.MUTED } className={ balanceText } />
						</Flex>
					</StyledCard>

					<StyledCard as={ Flex } direction="column" gap={ 4 } justify="flex-start">
						<StyledFeatureContainer gap={ 4 } align="flex-start">
							<StyledIcon><Icon icon={ icon } /></StyledIcon>
							<Heading
								level={ 3 }
								text={ feature }
								size={ TextSize.HUGE }
								variant={ TextVariant.DARK }
								weight={ TextWeight.NORMAL }
								className={ balanceHeading }
							/>
						</StyledFeatureContainer>
						{ children }
					</StyledCard>

					{ upsell && installType === 'free' && (
						<StyledCard as={ Flex } gap={ 4 } justify="flex-start" align="center" expanded={ false } compact>
							<ProTag />
							<Text text={ upsell } />
						</StyledCard>
					) }
				</StyledPrimaryContainer>
			</StyledLoginSecurityContent>

			<StyledContinueButton
				variant="primary"
				text={ __( 'Continue', 'better-wp-security' ) }
				icon={ continueIcon }
				iconPosition="right"
				onClick={ onContinue }
				isBusy={ isAnswering }
				disabled={ isAnswering }
			/>
		</StyledLoginSecurity>
	);
}
