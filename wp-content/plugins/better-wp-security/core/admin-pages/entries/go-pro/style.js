/**
 * External dependencices
 */
import styled from '@emotion/styled';

/**
 * SolidWP dependencies
 */
import { Button, Surface, Text } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { LogoColorPro, UpsellGradient } from '@ithemes/security-style-guide';

export const StyledMainContainer = styled( Surface )`
	position: relative;
	flex-grow: 1;
	display: flex;
	flex-direction: column;
	align-items: start;
	gap: 2rem;
	padding: 1.25rem;
	background: transparent !important;
`;

export const StyledMain = styled.main`
	position: relative;
	width: 100%;
	align-self: center;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
`;

export const StyledProLogo = styled( LogoColorPro )`
	max-width: 644px;
	margin: 0 auto;
	width: 100%;
`;

export const StyledUpsellGradient = styled( UpsellGradient )`
	opacity: 0.9;
	position: fixed;
	transform: translate(40%, 10%);
	filter: blur( 10px );
	z-index: 0;
`;

export const StyledUpsellText = styled( Text )`
	font-size: 50px;
	background-color: #79A9FF;
	background-image: linear-gradient(-45deg, #1F1F1F, #79A9FF);
	background-size: 100%;
	-webkit-background-clip: text;
	-moz-background-clip: text;
	-webkit-text-fill-color: transparent;
	-moz-text-fill-color: transparent;
	max-width: 610px;
	line-height: 3rem;
	z-index: 1;
  
	span {
		font-family: serif;
		font-style: italic;
	}
`;

export const StyledUpsellButton = styled( Button )`
	background: #1F1F1F !important;
	color: #FFFFFF;
	border-radius: 200px;
	font-size: 34px !important;
	padding: 2.5rem 3rem;
	margin-top: 5.5rem;
	z-index: 1;
`;
