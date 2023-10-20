/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';

/**
 * SolidWP dependencies
 */
import { Heading, Text } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { SolidLogoCropped } from '@ithemes/security-style-guide';

export const StyledBanner = styled.aside`
	position: relative;
	display: flex;
	flex-direction: column;
	gap: 1.5rem;
	margin: 1.25rem 1.25rem 0;
	background: ${ ( { theme } ) => theme.colors.surface.primary } ;
	padding: 1rem 2rem 1rem 1.5rem;
	justify-content: space-between;
	align-items: center;
	overflow: hidden;
	border-radius: 0.25rem;
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.medium }px) {
		flex-direction: row;
	}
`;

export const StyledTextContainer = styled.section`
	display: flex;
	flex-direction: column;
	gap: 0.625rem;
	align-items: center;
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.medium }px) {
		align-items: flex-start;
	}
`;

export const StyledBannerHeading = styled( Heading )`
	font-size: 1.5rem;
	text-align: center;
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.medium }px) {
		text-align: left;
	}
`;

export const StyledBannerButton = styled.a`
	display: inline-flex;
	min-width: max-content;
	padding: 0.75rem 1.75rem;
	justify-content: center;
	align-items: center;
	color: #ffffff;
	font-size: 0.83569rem;
	text-align: center;
	text-decoration: none;
	background: #6817C5;

	&:hover, &:active, &:focus {
		background-color: #53129e;
		color: ${ ( { theme } ) => theme.colors.text.white };
	}
`;

export const StyledStellarSaleDismiss = styled( Button )`
	position: absolute;
	top: 0;
	right: 0;
	&:hover, &:active, &:focus {
		color: #6817c5;
	}
`;

export const StyledBFCMBanner = styled.aside`
	display: flex;
	flex-direction: column;
	position: relative;
	overflow: hidden;
	margin: 1.25rem 1.25rem 0;
	padding: 0 2.5rem 0 2.5rem;
	background: linear-gradient(90deg, #00010C 0%, #8533FF 100%);
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.small }px) {
		flex-direction: row;
		justify-content: space-between;
		gap: 60px;
	}
`;

export const StyledBFCMTextContainer = styled.div`
	display: flex;
	flex-direction: column;
	align-items: flex-start;
	justify-content: center;
	gap: 0.5rem;
	margin-top: 2.5rem;
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.small }px) {
		margin-top: 0;
	}
`;

export const StyledBFCMHeading = styled( Heading )`
	font-size: 1.625rem;
	font-family: 'PolySans', sans-serif;
`;

export const StyledBFCMText = styled( Text )`
	font-size: 1.375rem;
	line-height: 1.5rem;
	font-family: 'PolySans', sans-serif;
`;

export const StyledBFCMButton = styled( Button )`
	height: 47px;
	width: 200px;
	margin-top: 1.25rem;
	padding: 15px 20px;
	justify-content: center;
	font-size: 1rem;
	border-radius: 50px;
	background: #F9FAF9;
	color: #8533FF;
`;

export const StyledLogo = styled( SolidLogoCropped )`
	max-width: 245px;
	margin-top: 2rem;
	margin-right: -1rem;
	align-self: flex-end;
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.medium }px) {
		margin-top: 2rem;
	}
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.xlarge }px) {
		margin-top: 1.25rem;
		margin-right: 90px;
	}
`;

export const StyledBFCMDismiss = styled( Button )`
	position: absolute;
	top: 2px;
	right: 2px;
	&:hover, &:active, &:focus {
		color: #6817c5;
	}
	
	& svg {
		background: white;
		border-radius: 25px;
	}
`;
