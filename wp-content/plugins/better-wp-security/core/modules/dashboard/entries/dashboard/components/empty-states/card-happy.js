/**
 * External Dependencies
 */
import styled from '@emotion/styled';

/**
 * Internal dependencies
 */
import { AllClearCard } from '@ithemes/security-style-guide';
import { HiResIcon } from '@ithemes/security-ui';
import {
	Heading,
	TextWeight,
	TextSize,
	Text, Surface,
} from '@ithemes/ui';

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

export default function CardHappy( { title, text } ) {
	return (
		<StyledSurface>
			<BodyContainer className="itsec-card__util-padding">
				<StyledSection>
					<Heading
						level={ 4 }
						size={ TextSize.NORMAL }
						weight={ TextWeight.HEAVY }
						text={ title }
						align="center"
					/>
					<Text
						as="p"
						text={ text }
						align="center"
					/>
				</StyledSection>
				<HiResIcon icon={ <AllClearCard /> } isSmall />
			</BodyContainer>
		</StyledSurface>
	);
}
