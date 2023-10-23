/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Icon } from '@wordpress/components';

/**
 * iTheme dependencies
 */
import { Heading, Surface, Text } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { Patchstack } from '@ithemes/security-style-guide';

export const StyledActions = styled.div`
	display: flex;
	flex-direction: ${ ( { isSmall } ) => isSmall && 'column' };
	justify-content: space-between;
	align-items: ${ ( { isSmall } ) => isSmall ? 'flex-start'
		: 'center' };
`;

export const StyledBadgesContainer = styled.div`
	display: flex;
	gap: 1rem;
`;

export const StyledCheck = styled( Icon )`
	background-color: #438C56;
	border-radius: 2rem;
`;

export const StyledSurfaceContainer = styled( Surface )`
	padding: 2rem;
`;

export const StyledHeader = styled( 'div', {
	shouldForwardProp: ( prop ) => prop !== 'isLarge',
} )
`
	display: flex;
	flex-direction: ${ ( { isLarge } ) => isLarge ? 'row' : 'column' };
	justify-content: space-between;
	gap: ${ ( { isLarge } ) => isLarge ? '6rem' : '1rem' };
`;

export const StyledTitle = styled( Heading )`
	margin-bottom: 1.5rem;
`;

export const StyledLogo = styled.div`
	display: flex;
	flex-direction: column;
	align-items: flex-end;
	order: ${ ( { isLarge } ) => ! isLarge && '-1' };
`;

export const StyledLogoText = styled( Text )`
	font-size: 0.625rem;
`;

export const StyledLogoImage = styled( Patchstack, {
	shouldForwardProp: ( prop ) => prop !== 'isLarge',
} )`
	width: ${ ( { isLarge } ) => isLarge ? '170px' : '124px' }
`;

export const StyledBadgeContainer = styled.div`
	display: flex;
	flex-direction: ${ ( { isLarge } ) => isLarge ? 'row' : 'column' };
	width: 100%;
	gap: 1.25rem;
`;

export const StyledBadgeSurface = styled( Surface )`
	display: flex;
	justify-content: center;
	align-items: center;
	gap: 1rem;
	width: 100%;
	padding: 1.25rem 0;
`;

export const StyledSeverity = styled( Text )`
  color: ${ ( { color } ) => color };
	
`;
export const StyledBadgeText = styled.div`
	display: flex;
	flex-direction: column;
`;

export const StyledVulnerabilitySection = styled.div`
	display: flex;
	flex-direction: column;
	gap: 0.875rem;
`;

export const StyledSectionTitle = styled( Heading )`
	padding-top: 2rem;
`;

export const StyledAlternateInstructions = styled( Text )`
	max-width: 90ch;
`;

export const StyledButtonsContainer = styled.div`
	display: flex;
	flex-wrap: wrap;
	gap: 1.5rem;
`;

export const StyledDataRow = styled.div`
	display: flex;
	flex-direction: ${ ( { isSmall } ) => isSmall && 'column' };
	justify-content: space-between;
	gap: ${ ( { isSmall } ) => isSmall && '0.5rem' };
	padding: 1.5rem 0;
	border-bottom: ${ ( { isLastRow, theme } ) => ! isLastRow && `1px solid ${ theme.colors.border.normal }` };
`;

export const StyledLabelWithIcon = styled.div`
	display: flex;
	align-items: center;
	gap: 0.75rem;
`;

export const StyledInfoIcon = styled( Icon )`
	fill: ${ ( { theme } ) => theme.colors.primary.darker20 }
`;

export const StyledTextWithIcon = styled.div`
	display: flex;
	align-items: center;
	gap: 1rem;
`;

export const StyledExternalContainer = styled.div`
	display: flex;
	justify-content: center;
	padding: 2rem 0 1rem;
`;
