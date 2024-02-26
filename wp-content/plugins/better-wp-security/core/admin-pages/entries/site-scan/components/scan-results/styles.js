import styled from '@emotion/styled';

import { Surface, Text } from '@ithemes/ui';

export const TabHeading = styled.div`
	padding: 1rem 1.5rem;
	border-radius: 2px 2px 0 0;
`;

export const StyledTabContainer = styled.div`
	border: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
	margin-top: 1.25rem;
`;

export const StyledTypeHeading = styled( Text )`
	width: 15%;
`;

export const StyledListHeading = styled( Text )`
	display: flex;
	justify-content: space-between;
	padding: 0.875rem;
	text-transform: uppercase;
	background-color: ${ ( { theme } ) => theme.colors.surface.tertiary };
	border-top: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
	border-bottom: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
`;

// Empty state
export const StyledNoScans = styled( Surface )`
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	gap: 1.25rem;
	padding: 2rem;
	max-width: 1680px;
`;
