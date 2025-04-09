/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * SolidWP dependencies
 */
import { Heading, Text, TextSize, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { HiResIcon } from '@ithemes/security-ui';
import { UnknownCrashCard } from '@ithemes/security-style-guide';
import {
	StyledSurface,
	BodyContainer,
	StyledSection,
} from './styles';

export default function LockoutsError( error ) {
	return (
		<StyledSurface>
			<BodyContainer>
				<StyledSection>
					<Heading
						level={ 4 }
						size={ TextSize.NORMAL }
						weight={ TextWeight.HEAVY }
						text={ __( 'Unexpected Error', 'better-wp-security' ) }
						align="center"
					/>
					<Text
						as="p"
						text={ __( 'An error occurred while rendering the list of lockouts.', 'better-wp-security' ) }
						align="center"
					/>
					<Text
						as="p"
						text={ __(
							'Try refreshing you browser. If the error persists, please contact support.',
							'better-wp-security'
						) }
						align="center"
					/>
					<Text
						as="p"
						text={ error.text }
						align="center"
					/>
				</StyledSection>
				<HiResIcon icon={ <UnknownCrashCard /> } isSmall />
			</BodyContainer>
		</StyledSurface>
	);
}
