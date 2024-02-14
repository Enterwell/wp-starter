/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * SolidWP dependencies
 */
import { Notice, SearchControl, Surface, Text } from '@ithemes/ui';

export const StyledLogsTable = styled( Surface )`
	border: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
`;

export const StyledTableHeader = styled.div`
	display: flex;
	flex-direction: column;
	gap: 0.5rem;
	padding: 1rem 1.5rem;
`;

export const StyledSubheading = styled( Text )`
	font-size: 0.75rem;
`;

export const StyledSearchControl = styled( SearchControl )`
	max-width: 360px;
	flex-grow: 1;
`;

export const StyledAction = styled( Text )`
	color: ${ ( { action, theme } ) => action === 'BLOCK' ? 'red' : theme.colors.text.dark };
	width: 80px;
`;

export const StyledRule = styled( Text )`
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.medium }px) {
		width: 22%;
		max-width: 300px;
	}
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.wide }px) {
		width: 30%;
	}
`;

export const StyledTableColumn = styled.div`
	display: flex;
	align-items: center;
	gap: 1rem;
	justify-content: space-between;
`;

export const StyledCombinedColumn = styled( StyledTableColumn )`
	justify-content: flex-start;
`;

export const StyledNotice = styled( Notice )`
	margin: 1rem;
`;

export const StyledNoResultsContainer = styled( Surface )`
	height: 275px;
`;

export const StyledEmptyState = styled( StyledNoResultsContainer )`
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	gap: 1.25rem;
	padding: 2.5rem 0;
`;

