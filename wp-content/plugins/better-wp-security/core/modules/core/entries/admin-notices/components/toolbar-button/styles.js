/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { ToolbarButton } from '@wordpress/components';

export const StyledTrigger = styled( ToolbarButton, {
	shouldForwardProp: ( propName ) => propName !== 'noticesCount',
} )`
	&::after {
		position: absolute;
		left: -6px;
		top: -6px;
		z-index: 1;
		min-width: 24px;
		padding: 4px;
		font-size: 11px;
		color: ${ ( { theme } ) => theme.colors.text.white };
		background: ${ ( { theme } ) => theme.colors.primary.base };
		border-radius: 12px;
		content: '${ ( { noticesCount } ) => noticesCount }';
		opacity: ${ ( { noticesCount } ) => noticesCount ? 1 : 0 };
		transition: opacity 1000ms ease-in-out;
	}
`;
