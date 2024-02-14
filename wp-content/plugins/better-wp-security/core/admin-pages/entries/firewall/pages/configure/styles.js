/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress components
 */
import { SnackbarList } from '@wordpress/components';

export const StyledSnackbarList = styled( SnackbarList )`
	.components-snackbar-list__notice-container {
		display: flex;
		flex-direction: column;
		align-items: flex-end;
		margin-right: 100px;
	}
`;
