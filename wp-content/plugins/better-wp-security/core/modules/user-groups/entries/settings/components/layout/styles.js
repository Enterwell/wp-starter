/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * Solid dependencies
 */
import { Surface } from '@ithemes/ui';

export const StyledContainer = styled( Surface )`
	border: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
`;
