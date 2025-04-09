/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * SolidWP dependencies
 */
import { Button } from '@ithemes/ui';

export const StyledActionsButton = styled( Button, { shouldForwardProp: ( propName ) => propName !== 'isActive' } )`
	box-shadow: inset 0 0 0 1px ${ ( { isActive } ) => isActive ?? '#545454' } !important;
	color: ${ ( { isActive, theme } ) => isActive ?? theme.colors.text.muted } !important;
	&:hover {
		background: ${ ( { isActive, theme } ) => isActive ?? theme.colors.surface.primaryContrast } !important;
	}
`;

