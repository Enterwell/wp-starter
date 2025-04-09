/**
 * External dependencies
 */
import styled from '@emotion/styled';

export { default as Title } from './title';

const StyledHeader = styled.header`
	display: flex;
	justify-content: ${ ( { align } ) => align === 'left' ? 'start' : 'space-between' };
	align-items: center;
	gap: 0.5rem;
	cursor: move;
	min-height: calc(36px + 1.5rem); // WP Button Height + Padding
	border-bottom: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
`;

export default function Header( { align = 'center', children } ) {
	return (
		<StyledHeader align={ align } className="itsec-card-header itsec-card__util-padding">
			{ children }
		</StyledHeader>
	);
}
