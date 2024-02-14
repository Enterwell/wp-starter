/**
 * External dependencies
 */
import styled from '@emotion/styled';

export const StyledAdminBar = styled.div`
	display: flex;
	flex-wrap: wrap;
	flex-direction: column;
	align-items: center;
	justify-items: center;
	gap: 1rem;
	padding: 1.25rem 1.25rem 0;
	margin: 0 auto;
	height: auto;
	max-width: ${ ( { maxWidth } ) => Math.max( maxWidth, 768 ) }px;

	@media (min-width: ${ ( { theme } ) => theme.breaks.medium }px) {
		flex-direction: row;
		justify-content: space-between;
	}
`;

export const StyledSection = styled.div`
	display: flex;
	align-items: center;
`;

export const StyledSectionPrimary = styled( StyledSection )`
	gap: 1.25rem;
`;

export const StyledSectionSecondary = styled( StyledSection )`
	gap: 3rem;
`;
