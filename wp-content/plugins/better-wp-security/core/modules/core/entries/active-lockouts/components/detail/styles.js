/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * SolidWP dependencies
 */
import { Text } from '@ithemes/ui';

export const StyledDetail = styled.div`
	padding: 0.5rem 1.25rem;
`;

export const StyledListItem = styled.li`
	display: flex;
	align-items: center;
	gap: 0.75rem;
`;

export const StyledHistoryLabel = styled( Text )`
	background-color: ${ ( { theme } ) => theme.colors.surface.secondary };
	padding: 11px 6px;
	border-radius: 2px;
`;
