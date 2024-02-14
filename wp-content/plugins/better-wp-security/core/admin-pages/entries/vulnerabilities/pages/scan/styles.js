/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Spinner } from '@wordpress/components';

export const StyledButtonsContainer = styled.div`
	display: flex;
	gap: 1rem;
	margin-left: auto;
`;

export const ScanningContainer = styled.div`
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 0.75rem;
	margin: 0 auto;
	padding: 70px 0;
	max-width: 36ch;
`;

export const StyledSpinner = styled( Spinner )`
	width: 80px !important;
	height: 80px !important;
`;
