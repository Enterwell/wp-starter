/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * Solid dependencies
 */
import { Surface } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { Logo } from '@ithemes/security-ui';

export const StyledMainContainer = styled( Surface )`
	position: relative;
	height: auto;
	flex-grow: 1;
	display: flex;
	flex-direction: column;
	align-items: start;
	gap: 2rem;
	padding: 1.25rem;
`;

export const StyledMain = styled.main`
	position: relative;
	width: 100%;
	align-self: center;
	display: flex;
	flex-direction: column;
	flex-grow: 1;
	align-items: center;
	justify-content: center;
`;

export const StyledGraphic = styled( Graphic )`
	position: absolute;
	top: 0;
	right: 0;
	pointer-events: none;
`;

export const StyledLogo = styled( Logo )`
	height: 44px;
	width: auto;
`;

function Graphic( { className } ) {
	return (
		<svg
			className={ className }
			width="579"
			height="503"
			viewBox="0 0 579 503"
			fill="none"
			xmlns="http://www.w3.org/2000/svg"
		>
			<g opacity="0.6" filter="url(#filter0_bf_5527_127309)">
				<ellipse cx="579" cy="-56.5" rx="259" ry="239.5" fill="#53129E" />
			</g>
			<defs>
				<filter id="filter0_bf_5527_127309" x="0" y="-616" width="1158" height="1119" filterUnits="userSpaceOnUse" colorInterpolationFilters="sRGB">
					<feFlood floodOpacity="0" result="BackgroundImageFix" />
					<feGaussianBlur in="BackgroundImageFix" stdDeviation="20" />
					<feComposite in2="SourceAlpha" operator="in" result="effect1_backgroundBlur_5527_127309" />
					<feBlend mode="normal" in="SourceGraphic" in2="effect1_backgroundBlur_5527_127309" result="shape" />
					<feGaussianBlur stdDeviation="160" result="effect2_foregroundBlur_5527_127309" />
				</filter>
			</defs>
		</svg>
	);
}
