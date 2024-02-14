import styled from '@emotion/styled';
import { Filters } from '@ithemes/ui';

export const StyledFilters = styled( Filters )`
	width: 350px;
`;

export const StyledFilterTools = styled.div`
	display: flex;
	align-items: center;
	gap: 1rem;
`;

export const StyledSearchDivider = styled.span`
	color: #c0c0c0;
`;

export const StyledButtonsContainer = styled.div`
	display: flex;
	flex-direction: ${ ( { isSmall } ) => isSmall && 'column' };
	gap: 1rem;
`;

export const StyledPagination = styled.div`
	display: flex;
	gap: 2rem;
	justify-content: flex-end;
	& .components-button.is-tertiary:disabled {
		background: transparent !important;
	}
`;
