/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * SolidWP dependencies
 */
import { Button, Surface } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { Patchstack } from '@ithemes/security-style-guide';

export const StyledAsideHeader = styled.div`
	max-width: 280px;
	display: flex;
	flex-direction: column;
	flex-wrap: wrap;
	align-content: center;
	justify-content: space-around;
	align-items: flex-end;
	gap: 1rem;
	margin-bottom: -3rem;
`;

export const StyledPatchstackBanner = styled( Surface )`
	position: relative;
	display: flex;
	flex-direction: column;
	justify-content: space-between;
	align-items: flex-start;
	gap: 0.5rem;
	padding: 1.25rem 0.75rem;
	border-radius: 2px;
	border-left: 4px solid ${ ( { theme } ) => theme.colors.primary.base };
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.medium }px) {
		flex-direction: row;
		gap: 2rem;
		align-items: center;
	}
`;

export const StyledTextContainer = styled.div`
	display: flex;
	flex-direction: column;
	gap: 0.5rem;
`;

export const StyledPatchstackLogo = styled( Patchstack )`
	width: 150px;
	height: 1.25rem;
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.medium }px) {
		width: 115px;
		height: 1rem;
	}
`;

export const StyledPatchstackButton = styled( Button )`
	background: ${ ( { theme } ) => theme.colors.surface.dark } !important;
	color: ${ ( { theme } ) => theme.colors.text.white } !important;
	box-shadow: inset 0 0 0 1px transparent !important;
	padding: 0.5rem 0.75rem;
	border-radius: 20px;
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.medium }px) {
		width: 100%;
		max-width: 200px;
	}
`;

export const StyledPatchstackDismiss = styled( Button )`
	position: absolute;
	top: 0;
	right: 0;
	box-shadow: none !important;
	color: ${ ( { theme } ) => theme.colors.text.normal } !important;
	&:hover, &:active, &:focus {
		color: ${ ( { theme } ) => theme.colors.text.accent } !important;
	}
`;

export const StyledBeforeCreateRulePromo = styled( Surface )`
	padding: 1.25rem 1.5rem;
	border-radius: 2px;
`;
