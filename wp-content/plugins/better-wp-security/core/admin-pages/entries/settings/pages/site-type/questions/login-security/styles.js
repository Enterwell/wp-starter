/**
 * External dependencies
 */
import styled from '@emotion/styled';
import { css } from '@emotion/css';

/**
 * WordPress dependencies
 */
import { Flex } from '@wordpress/components';

/**
 * SolidWP dependencies
 */
import { Button, Surface } from '@ithemes/ui';

export const StyledLoginSecurity = styled( Surface )`
	display: flex;
	flex-direction: column;
	gap: 4rem;
	max-width: 95ch;
	width: 100%;
`;

export const StyledContinueButton = styled( Button )`
	justify-content: center !important;
`;

export const StyledLoginSecurityContent = styled.div`
	display: flex;
	gap: 2rem;
	width: 100%;
	flex-wrap: wrap;
`;

export const StyledGraphicContainer = styled.figure`
	background: black;
	background-image: radial-gradient(circle at 56% -151%, #5d35ff, #5933ef 43%, #2f2352 66%, #261c43 75%, rgba(35, 35, 35, 0) 95%);
	flex: 2;
	padding: 20px 0 90px 0;
	margin: 0;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	position: relative;
	
	& > svg {
		fill: white;
		opacity: 0.75;
		margin-bottom: 20px;
	}
`;

export const StyledPrimaryContainer = styled.div`
	flex: 3;
	display: flex;
	flex-direction: column;
	gap: 2rem;
`;

export const StyledCard = styled( Surface, {
	shouldForwardProp: ( prop ) => prop !== 'compact',
} )`
	border: 1px solid ${ ( { theme } ) => theme.colors.border.muted };
	border-radius: 0.25rem;
	padding: ${ ( { compact } ) => compact ? '1rem 1.5rem' : '1.5rem' };
`;

export const StyledIcon = styled.figure`
	display: flex;
	align-items: center;
	justify-content: center;
	width: 2.25rem;
	height: 2.25rem;
	margin: 0;
	flex-shrink: 0;
	border-radius: 50%;
	background: ${ ( { theme } ) => theme.colors.text.dark };

	svg {
		fill: ${ ( { theme } ) => theme.colors.text.white };
	}
`;

export const StyledFeatureContainer = styled( Flex )`
	margin-bottom: 0.5rem;
`;

export const balanceHeading = css`
	text-wrap: balance; // Fallback for Webkit and Firefox.
	text-wrap: pretty;
`;

export const balanceText = css`
	text-wrap: pretty;
`;
