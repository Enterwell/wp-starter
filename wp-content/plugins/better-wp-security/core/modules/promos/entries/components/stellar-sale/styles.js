/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * iThemes dependencies
 */
import { Text, Heading } from '@ithemes/ui';

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';

export const StyledStellarSale = styled.aside`
	position: relative;
	display: flex;
	margin: 1.25rem 1.25rem 0;
	background: #1D202F;
	color: #F9FAF9;
	padding: 1rem;
	justify-content: space-between;
	overflow: hidden;
`;

export const StyledStellarSaleDismiss = styled( Button )`
	color: white;
	z-index: 2;

	&:hover, &:active, &:focus {
		color: white !important;
	}
`;

export const StyledStellarSaleContent = styled.div`
	max-width: 60rem;
	display: grid;
	grid-template-columns: ${ ( { isSmall } ) => isSmall ? '1fr 1fr' : 'auto' };
	gap: 1rem 1.5rem;
	align-items: end;
	justify-items: start;
	padding: ${ ( { isSmall } ) => isSmall
		? '1.25rem 4.45rem 0.65rem 2.9rem'
		: '1.25rem 4.45rem 0.65rem 0.25rem'
};
`;

export const StyledStellarSaleHeading = styled( Heading )`
	grid-column: ${ ( { isSmall } ) => ! isSmall && 'span 2' };
  
	strong {
		font-size: 1.5rem;
	}
`;

export const StyledStellarSaleButton = styled.a`
	display: inline-flex;
	min-width: max-content;
	padding: 0.75rem 1.75rem;
	justify-content: center;
	align-items: center;
	color: #ffffff;
	font-size: 0.83569rem;
	text-align: center;
	text-transform: uppercase;
	text-decoration: none;
	border-radius: 7.8125rem;
	background: #6817C5;

	&:hover, &:active, &:focus {
		color: inherit;
		opacity: 0.75;
	}
`;

export const StyledStellarSaleLink = styled( Text )`
	text-decoration: underline;
	align-self: ${ ( { isSmall } ) => isSmall ? 'start' : 'center' };

	&:hover, &:active, &:focus {
		color: inherit;
		font-style: oblique;
	}
`;

export const StyledStellarSaleGraphic = styled( Graphic )`
	position: absolute;
	right: 0;
	bottom: ${ ( { isHuge } ) => isHuge ? '-1rem' : '-2rem' };
`;

function Graphic( { className, isHuge } ) {
	if ( ! isHuge ) {
		return (
			<svg
				className={ className }
				width="116"
				height="167"
				viewBox="0 0 116 167"
				fill="none"
				xmlns="http://www.w3.org/2000/svg"
			>
				<circle cx="9.58348" cy="9.58348" r="9.58348" fill="#F9FAF9" />
				<path d="M245.378 48.0009L103.543 222.42L9.625 9.66699L245.378 48.0009ZM245.378 48.0009L306.713 126.586" stroke="#F9FAF9" strokeWidth="1.43752" />
			</svg>
		);
	}

	return (
		<svg
			className={ className }
			width="280"
			height="154"
			viewBox="0 0 280 154"
			fill="none"
			xmlns="http://www.w3.org/2000/svg"
		>
			<g clipPath="url(#clip0_1482_390)">
				<circle cx="10.2895" cy="9.58348" r="9.58348" fill="#F9FAF9" />
				<circle cx="245.085" cy="46.9587" r="4.79174" fill="#F9FAF9" />
				<path d="M245.085 46.0009L103.249 220.42L9.33105 7.66699L245.085 46.0009ZM245.085 46.0009L306.419 124.586" stroke="#F9FAF9" strokeWidth="1.43752" />
			</g>
			<defs>
				<clipPath id="clip0_1482_390">
					<rect width="280" height="154" fill="white" />
				</clipPath>
			</defs>
		</svg>
	);
}
