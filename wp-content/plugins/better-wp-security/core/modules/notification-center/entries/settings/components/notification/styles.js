/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * Solid dependencies
 */
import { Surface } from '@ithemes/ui';

export const StyledNotification = styled( Surface )`
	border: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
`;

export const StyledTagList = styled.dl`
	margin: 0;
`;

export const StyledTagName = styled.dt`
	margin-top: 1rem;
`;

export const StyledTagDescription = styled.dd`
	margin: .25rem 0 0 0;	
`;
