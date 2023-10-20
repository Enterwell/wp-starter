/**
 * External dependencies
 */
import styled from '@emotion/styled';

export const StyledPagination = styled.div`
	display: flex;
	gap: 2rem;
	justify-content: flex-end;
	& .components-button.is-tertiary:disabled {
		background: transparent !important;
	}
`;
