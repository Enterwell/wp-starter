/**
 * External dependencies
 */
import styled from '@emotion/styled';

export const StyledPageContainer = styled.div`
	display: flex;
	flex-direction: column;
	gap: 1rem;
	width: 100%;
	max-width: 1680px;
	margin: 0 auto;
	padding: 1rem 1.25rem;
`;

export const StyledPageHeader = styled.header`
	display: flex;
	justify-content: space-between;
	align-items: ${ ( { isSmall } ) => isSmall ? 'flex-start' : 'center' };
`;
