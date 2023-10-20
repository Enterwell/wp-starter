/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * Solid dependencies
 */
import { Filters, SearchControl } from '@ithemes/ui';

export const StyledRulesTableHeader = styled.form`
	padding: 1rem 1.25rem;
	display: flex;
	flex-direction: column;
	gap: 0.25rem;
`;

export const StyledSearchContainer = styled.div`
	display: flex;
	align-items: center;
	gap: 0.5rem;
	margin-top: 0.25rem;
`;

export const StyledSearchControl = styled( SearchControl )`
	max-width: 360px;
	flex-grow: 1;
`;

export const StyledFilters = styled( Filters )`
	width: 350px;
`;

export const StyledSearchDivider = styled.span`
	color: #c0c0c0;
`;
