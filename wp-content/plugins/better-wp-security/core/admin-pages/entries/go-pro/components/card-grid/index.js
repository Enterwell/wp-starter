/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Children } from '@wordpress/element';

const StyledGrid = styled.div`
	width: auto;
	display: grid;
	grid-template-columns: repeat(1, 1fr);
	justify-items: center;
	justify-content: center;
	gap: 3rem;

	@media (min-width: ${ ( { theme } ) => theme.breaks.medium }px) {
		grid-template-columns: repeat(min(2, ${ ( { count } ) => count }), 1fr);
	}

	@media (min-width: ${ ( { theme } ) => theme.breaks.wide }px) {
		grid-template-columns: repeat(min(3, ${ ( { count } ) => count }), 1fr);
	}
`;

export default function CardGrid( { children } ) {
	return (
		<StyledGrid className="itsec-go-pro-card-grid" count={ Children.count( children ) }>
			{ children }
		</StyledGrid>
	);
}
