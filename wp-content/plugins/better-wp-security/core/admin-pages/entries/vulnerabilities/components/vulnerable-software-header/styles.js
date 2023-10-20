/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * Solid WP dependencies
 */
import { Heading, Text } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { Patchstack } from '@ithemes/security-style-guide';

export const StyledHeader = styled.header`
	display: flex;
	flex-direction: ${ ( { isSmall } ) => isSmall && 'column' };
	justify-content: space-between;
	align-items: ${ ( { isSmall } ) => isSmall ? 'flex-start' : 'center' };
	padding: 1rem 1.25rem;
`;

export const StyledHeading = styled( Heading )`
	margin-top: ${ ( { isSmall } ) => isSmall && '-1.25rem' };
`;

export const StyledLastScanDate = styled( Text, {
	shouldForwardProp: ( prop ) => prop !== 'hasScanDate',
} )`
	visibility: ${ ( { hasScanDate } ) => hasScanDate ? 'visible' : 'hidden' };
`;

export const StyledBadgesContainer = styled.div`
	display: flex;
	flex-direction: ${ ( { isSmall } ) => isSmall && 'column' };
	gap: 1rem;
	margin-top: 1rem;
`;

export const StyledBrand = styled.div`
	display: flex;
	flex-direction: column;
	align-items: flex-end;
	order: ${ ( { isSmall } ) => isSmall && '-1' };
	align-self: ${ ( { isSmall } ) => isSmall && 'flex-end' };
`;

export const StyledLogoText = styled( Text )`
	font-size: 0.625rem;
`;

export const StyledLogoImage = styled( Patchstack, {
	shouldForwardProp: ( prop ) => prop !== 'isLarge',
} )`
	width: ${ ( { isLarge } ) => isLarge ? '170px' : '124px' }
`;
