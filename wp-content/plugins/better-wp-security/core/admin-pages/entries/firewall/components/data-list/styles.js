/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * SolidWP dependencies
 */
import { Button, Surface } from '@ithemes/ui';

export const StyledListContainer = styled.div`
	display: flex;
	flex-direction: column;
	flex-grow: 1;
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.wide }px) {
		flex-grow: 0
	}
`;

export const StyledListItem = styled.div`
	margin: 0.75rem 0;
`;

export const StyledHeadingRow = styled( Surface )`
	display: flex;
	gap: 0.5rem;
	padding: 8px 12px;
	border-radius: 2px 2px 0 0;
`;

export const StyledBodyRow = styled( Surface )`
	display: flex;
	justify-content: space-between;
	padding: 8px 12px;
	border-top-left-radius: ${ ( { hasHeading } ) => ! hasHeading && '2px' };
	border-top-right-radius: ${ ( { hasHeading } ) => ! hasHeading && '2px' };
	:last-of-type {
		border-bottom-right-radius: 2px;
		border-bottom-left-radius: 2px;
	}
`;

export const StyledEmptyState = styled.div`
	display: flex;
	flex-direction: column;
	gap: 0.75rem;
`;

export const StyledEmptySurface = styled( Surface )`
	display: flex;
	flex-direction: column;
	gap: 0.75rem;
	margin: 0.75rem 0;
	padding: 1rem 0.75rem;
`;

export const StyledSettingsLink = styled( Button )`
	width: fit-content;
	align-self: center;
`;
