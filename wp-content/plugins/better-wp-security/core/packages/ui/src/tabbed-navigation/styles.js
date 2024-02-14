/**
 * External dependencies
 */
import styled from '@emotion/styled';
import { NavLink } from 'react-router-dom';

/**
 * Solid dependencies
 */
import { Text } from '@ithemes/ui';

export const StyledNav = styled.nav`
	display: flex;
	border-bottom: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
`;

export const StyledTab = styled( NavLink )`
	display: flex;
	text-decoration: none;
	align-items: center;

	&.active {
		box-shadow: inset 0 -4px 0 0 ${ ( { theme } ) => theme.colors.border.info };
	}
	&:focus {
		color: ${ ( { theme } ) => theme.colors.text.dark };
		box-shadow: inset 0 0 0 2px ${ ( { theme } ) => theme.colors.border.info },
					inset 0 -4px 0 0 ${ ( { theme } ) => theme.colors.border.info } !important;
		border-radius: 3px !important;
`;

export const StyledTabTitle = styled( Text )`
	padding: .75rem 1.25rem;
`;
