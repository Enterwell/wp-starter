/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * iTheme dependencies
 */
import { Surface } from '@ithemes/ui';

export const StyledApp = styled( Surface )`
	display: flex;
	flex-direction: column;
`;

export const StyledPageContainer = styled.div`
	gap: ${ ( { theme: { getSize } } ) => getSize( 1 ) };
	width: 100%;
	max-width: 1680px;
	margin: 0 auto 2rem;
	padding: ${ ( { theme: { getSize } } ) => `${ getSize( 1 ) } ${ getSize( 1.25 ) }` };

`;

export const StyledHeadingText = styled.div`
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding-bottom: ${ ( { theme: { getSize } } ) => getSize( 1 ) };
`;

export const StyledScanSurface = styled( Surface )`
	display: flex;
	flex-direction: column;
	gap: ${ ( { theme: { getSize } } ) => `${ getSize( 2.5 ) } 0` };
	padding: ${ ( { theme: { getSize } } ) => `${ getSize( 1 ) } ${ getSize( 1.25 ) }` };
`;

export const StyledActionButtons = styled.div`
	display: flex;
	align-items: center;
	gap: 1rem;
`;

