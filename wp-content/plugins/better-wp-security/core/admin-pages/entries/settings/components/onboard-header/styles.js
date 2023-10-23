/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * Solid dependencies
 */
import { Button, Heading, Text } from '@ithemes/ui';

export const StyledOnboardHeader = styled.header`
	display: grid;
	grid-template-areas: "title action" "description action";
	grid-gap: 0.5rem 1rem;
	margin-bottom: 1.5rem;
`;

export const StyledOnboardTitle = styled( Heading )`
	grid-area: title;
	display: flex;
	gap: 0.5rem;
`;

export const StyledOnboardDescription = styled( Text )`
	grid-area: description;
`;

export const StyledOnboardAction = styled( Button )`
	grid-area: action;
	align-self: end;
	justify-self: end;
`;
