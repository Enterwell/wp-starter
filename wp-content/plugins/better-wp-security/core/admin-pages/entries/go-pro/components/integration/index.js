/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Card, CardBody } from '@wordpress/components';
import { check as checkIcon } from '@wordpress/icons';

/**
 * iThemes dependencies
 */
import { Heading, Text, List, ListItem, Button, TextWeight } from '@ithemes/ui';

const StyledCard = styled( Card )`
	&.components-card {
		max-width: 460px;
		border-radius: 16px;
	}

	> div {
		display: flex;
		flex-direction: column;
	}
`;

const StyledBody = styled( CardBody )`
	display: flex;
	flex-direction: column;
	flex-grow: 1;
`;

const StyledHeader = styled.header`
	margin-bottom: 1rem;
`;

const StyledImg = styled.img`
	margin: 2rem 0;
	height: 55px;
	align-self: start;
`;

const StyledList = styled( List )`
	margin-top: 1rem;
`;

const StyledButton = styled( Button )`
	width: 90%;
	margin: 1.5rem 0 1rem;
	align-self: center;
	justify-content: center;
`;

export default function Integration( { imageSrc, heading, subheading, description, features, buttonText, buttonHref } ) {
	return (
		<StyledCard elevation={ 5 }>
			<StyledBody>
				<StyledImg src={ imageSrc } alt="" aria-hidden />
				<StyledHeader>
					<Heading level={ 3 } size="large" weight={ TextWeight.HEAVY } variant="dark" text={ heading } />
					<Heading level={ 4 } size="large" weight={ TextWeight.HEAVY } variant="dark" text={ subheading } />
				</StyledHeader>
				<div aria-hidden style={ { flexGrow: 1 } } />
				<Text variant="muted" as="p" text={ description } />
				<StyledList textVariant="dark" icon={ checkIcon } textWeight={ TextWeight.HEAVY }>
					{ features.map( ( feature, i ) => (
						<ListItem key={ i } text={ feature } />
					) ) }
				</StyledList>
				<StyledButton isRounded isWide variant="primary" textSize="large" text={ buttonText } href={ buttonHref } />
			</StyledBody>
		</StyledCard>
	);
}
