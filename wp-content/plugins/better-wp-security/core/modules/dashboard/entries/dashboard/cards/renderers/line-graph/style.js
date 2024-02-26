/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * SolidWP dependencies
 */
import { Surface } from '@ithemes/ui';

export const StyledTooltip = styled( Surface )`
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	padding: 4px 8px;
	border-radius: 2px;
`;

export const StyledEmptyStateContainer = styled.div`
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	height: 275px;
	gap: 1rem;
	padding: 2rem;
`;
