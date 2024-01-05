/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { SnackbarList } from '@wordpress/components';

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

// Remove snackbar styling once core issue is resolved
// Link to Issue https://github.com/WordPress/gutenberg/issues/56126
export const StyledSnackbarList = styled( SnackbarList )`
	.components-snackbar {
		margin: 0 auto;
	}
  
	.components-snackbar__content {
		position: relative;
		gap: 1rem;
		align-items: center;
	}
	
	.components-snackbar .components-snackbar__content-with-icon {
		margin-left: 0;
	}
  
	.components-snackbar .components-snackbar__icon {
		position: relative;
		left: 0;
		top: 0;
	}
`;
