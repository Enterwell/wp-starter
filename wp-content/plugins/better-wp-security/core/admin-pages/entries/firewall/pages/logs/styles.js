/**
 * External dependencies
 */
import styled from '@emotion/styled';

export const StyledColumnsContainer = styled.div`
	display: flex;
	flex-direction: column;
	gap: 1.5rem;
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.wide }px) {
		flex-direction: row;
	}
`;

export const StyledCardsContainer = styled.div`
	display: flex;
	flex-direction: column;
	gap: 1rem;
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.huge }px) {
		flex-grow: 1.5;
		gap: 2rem;
	}
`;

export const StyledListsContainer = styled.div`
	display: flex;
	flex-direction: column;
	gap: 2rem;
	min-width: 300px;

	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.medium }px) {
		flex-direction: row;
		gap: 3.5rem;
	}
	
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.wide }px) {
		flex-direction: column;
		border-left: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
		padding-left: 1.25rem;
	}
`;
