/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * iThemes dependencies
 */
import {
	Heading,
	Surface,
	Text,
	TextSize,
	TextWeight,
} from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { UnknownCrashCard } from '@ithemes/security-style-guide';
import { HiResIcon } from '@ithemes/security-ui';
import Header, { Title } from '../card/header';

const StyledSurface = styled( Surface )`
	display: flex;
	flex-direction: column;
	height: 100%;
`;

const BodyContainer = styled.div`
	text-align: center;
	display: flex;
	gap: ${ ( { theme: { getSize } } ) => getSize( 1.25 ) };
	flex-direction: column;
	align-items: center;
	flex-grow: 1;
	height: 100%;
	justify-content: center;
`;

const StyledSection = styled.section`
	max-width: 70ch;
	display: flex;
	flex-direction: column;
	gap: ${ ( { theme: { getSize } } ) => getSize( 0.5 ) };
`;

function CardCrash( { card, config } ) {
	return (
		<StyledSurface>
			{ config && (
				<Header>
					<Title card={ card } config={ config } />
				</Header>
			) }
			<BodyContainer className="itsec-card__util-padding">
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
						text={ __( 'An error occurred while rendering this card.', 'better-wp-security' ) }
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
				</StyledSection>
				<HiResIcon icon={ <UnknownCrashCard /> } isSmall />
			</BodyContainer>
		</StyledSurface>
	);
}

export default CardCrash;
