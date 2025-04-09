/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * SolidWP dependencies
 */
import { Surface } from '@ithemes/ui';

export const StyledSectionCreate = styled( Surface )`
	padding: ${ ( { theme: { getSize } } ) => getSize( 1 ) };
	flex-grow: 1;
	overflow: auto;
`;
