/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Icon } from '@wordpress/components';

/**
 * Solid dependencies
 */
import { Heading, Surface, Text } from '@ithemes/ui';

export const StyledButtonWrapper = styled.button`
	text-decoration: none;
	margin: 0;
	border: 0;
	padding: 0;
	cursor: pointer;
	-webkit-appearance: none;
	background: none;
	width: 100%;

	&:focus,
	&[aria-pressed="true"] {
		box-shadow: 0 0 0 var(--wp-admin-border-width-focus) ${ ( { theme } ) => theme.colors.primary.base };
		outline: 3px solid transparent;
	}
`;

export const StyledTitle = styled( Heading )`
	grid-area: title;
`;

export const StyledDescription = styled( Text )`
	grid-area: description;
`;

export const StyledIconContainer = styled( Surface )`
	grid-area: icon;
	width: 4rem;
	height: 3.25rem;
	display: flex;
	align-items: center;
	justify-content: center;
	border-radius: 2px;
`;

export const StyledIcon = styled( Icon )`
	fill: #9675F7;
`;

export const StyledGoIcon = styled( Icon )`
	grid-area: go;
	align-self: center;
`;

export const StyledSelectableCard = styled.div`
	background: #f6f7f7;
	border: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
	border-radius: 2px;
	display: ${ ( { direction } ) => direction === 'horizontal' ? 'grid' : 'flex' };
	flex-direction: column;
	align-items: ${ ( { direction } ) => direction === 'vertical' && 'center' };
	justify-content: ${ ( { direction } ) => direction === 'vertical' && 'center' };
	grid-template-areas: "icon title go" "icon description go";
	grid-template-columns: min-content auto min-content;
	gap: ${ ( { direction } ) => direction === 'horizontal' ? '0.5rem 1.25rem' : '0.75rem' };
	padding: 1rem;
	min-height: ${ ( { direction } ) => direction === 'vertical' && '200px' };

	&:hover ${ StyledGoIcon } {
		fill: ${ ( { theme } ) => theme.colors.primary.darker20 };
	}

	&:hover ${ StyledTitle } {
		color: ${ ( { theme } ) => theme.colors.primary.darker20 };
	}
`;
