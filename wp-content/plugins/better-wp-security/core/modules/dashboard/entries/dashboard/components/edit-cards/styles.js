/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * iThemes dependencies
 */
import { Button, Surface } from '@ithemes/ui';

export const StyledEditCards = styled( Surface )`
	width: ${ ( { isExpanded } ) => isExpanded ? '100%' : '300px' };
	padding: 0.25rem 0.75rem;
	display: flex;
	flex-direction: column;
	gap: 0.5rem;
`;

export const StyledHeader = styled.header`
	display: flex;
	flex-direction: column;
	gap: 0.5rem;
`;

export const StyledCardsList = styled.ul`
	margin: 0;
	display: flex;
	flex-direction: column;
	gap: 0.75rem;
`;

export const StyledCard = styled.li`
	margin: 0;
`;

export const StyledButton = styled( Button )`
	width: 100%;
	justify-content: left !important;
`;

export const StyledTertiaryButton = styled( StyledButton )`
	&.components-button {
		background-color: ${ ( { theme } ) => theme.colors.surface.secondary };
	}
	display: grid;
	grid-template-columns: 1fr 0.1fr;
	text-align: left;
	padding-left: 0.75rem !important;
`;
