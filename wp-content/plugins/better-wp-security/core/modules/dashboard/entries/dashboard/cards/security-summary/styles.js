/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Icon } from '@wordpress/components';

/**
 * SolidWP dependencies
 */
import { Surface, Text } from '@ithemes/ui';

export const StyledStar = styled( Icon )`
	fill: ${ ( { theme } ) => theme.colors.primary.darker20 }
`;

export const StyledSection = styled.section`
	padding: 0.75rem 1.25rem;
	display: flex;
	flex-direction: column;
	gap: ${ ( { isSmall } ) => isSmall ? '0.75rem' : '1rem' };

	&:first-of-type {
		padding-top: ${ ( { isSmall } ) => ! isSmall && '1.25rem' };
	}
`;

export const StyledSectionTitle = styled( Surface )`
	display: flex;
	align-items: center;
	gap: 1rem;
`;

// === News === //

export const StyledBlogLink = styled.a`
	text-decoration: none;
`;

export const StyledBlogItem = styled.div`
	border: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: 0.25rem;
	padding: 0.5rem 0.75rem;
`;

export const StyledBlogText = styled.div`
	display: flex;
	flex-direction: column;
	gap: 0.5rem;
`;

export const StyledBlogIcon = styled( Icon )`
	flex-shrink: 0;
`;

// === Vulnerability === //

export const StyledVulnerability = styled( Surface )`
	display: flex;
	align-items: center;
	gap: 0.5rem;
`;

export const StyledSeverity = styled( Text )`
	& span {
		padding: 1.5px 6.5px;
		background-color: ${ ( { backgroundColor } ) => backgroundColor };
		border-radius: 2px;
	}
`;

export const StyledDetail = styled( Text )`
	grid-area: details;
`;
