/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * Solid dependencies
 */
import { List, Text } from '@ithemes/ui';

export const StyledChildPages = styled( List )`
	margin: 1rem;
`;

export const StyledChildLink = styled( Text )`
	text-decoration: none;

	&.active {
		color: ${ ( { theme } ) => theme.colors.primary.darker20 };
	}
`;
