/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * iThemes dependencies
 */
import { List, Surface } from '@ithemes/ui';

export const StyledPanel = styled( Surface )`
	display: flex;
	flex-direction: column;
	gap: 1rem;
	width: 320px;
	padding: 1.25rem 1rem;
	box-sizing: border-box;
	border: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
`;

export const StyledHeader = styled.header`
	display: flex;
	gap: 0.5rem;
	align-items: center;
	justify-content: space-between;
	border-bottom: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
	padding-bottom: 1rem;
`;

export const StyledHeaderText = styled.div`
	display: flex;
	flex-direction: column;
	gap: 0.5rem;
`;

export const StyledHighlightsList = styled( List )`
	border-bottom: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
	padding-bottom: 1rem;
`;
