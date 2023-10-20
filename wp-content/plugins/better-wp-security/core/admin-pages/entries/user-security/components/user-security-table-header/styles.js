import styled from '@emotion/styled';

export const StyledTableHeader = styled.header`
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: ${ ( { theme: { getSize } } ) =>
		`${ getSize( 1 ) } ${ getSize( 1.5 ) }` };
`;
