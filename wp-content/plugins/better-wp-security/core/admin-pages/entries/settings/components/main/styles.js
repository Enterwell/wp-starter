/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { FlexItem } from '@wordpress/components';

export const StyledMainContainer = styled.div`
	display: flex;
	align-self: center;
	flex-direction: column;
	gap: 1.25rem;
	width: 100%;
	max-width: 1680px;
	padding: 1.25rem 1.25rem 4rem 1.5rem;
`;

export const StyledMain = styled.main`
	position: relative;
`;

export const StyledNavigationContainer = styled( FlexItem )`
	margin-top: ${ ( { isMedium } ) => isMedium && '1.25rem' };
	width: ${ ( { isMedium } ) => isMedium && '10rem' };
`;
