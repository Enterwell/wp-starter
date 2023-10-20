/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { SnackbarList } from '@wordpress/components';

/**
 * SolidWP dependencies
 */
import { Surface } from '@ithemes/ui';

export const StyledApp = styled( Surface )`
	display: flex;
	flex-direction: column;
`;

export const StyledPageContainer = styled.div`
	display: flex;
	flex-direction: column;
	gap: 1rem;
	width: 100%;
	max-width: 1680px;
	margin: 0 auto;
	padding: ${ ( { theme: { getSize } } ) =>
		`${ getSize( 1 ) } ${ getSize( 1.25 ) }` };
`;

export const StyledPageHeader = styled.header`
	padding: ${ ( { theme: { getSize } } ) =>
		`${ getSize( 1 ) } 0` };
`;

export const StyledSnackbarList = styled( SnackbarList )`
	.components-snackbar {
		margin: 0 auto;
	}
`;
