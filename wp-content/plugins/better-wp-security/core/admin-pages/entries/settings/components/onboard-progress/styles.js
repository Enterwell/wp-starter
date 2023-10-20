/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * Solid dependencies
 */
import { Text } from '@ithemes/ui';

export const StyledOnboardProgress = styled.div`
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 0.5rem;
	margin-top: auto;
	align-self: center;
`;

export const StyledOnboardProgressList = styled.ul`
	display: flex;
	gap: 1.5rem;
	justify-content: center;
	align-items: center;
	margin: 0;
	padding: 0;
`;

export const StyledOnboardProgressItem = styled( Text )`
	display: inline-flex;
	align-items: center;
	gap: 0.25rem;
`;
