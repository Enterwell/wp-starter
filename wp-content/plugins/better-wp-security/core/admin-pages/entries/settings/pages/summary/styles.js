/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Flex } from '@wordpress/components';

/**
 * Solid dependencies
 */
import { List, Button } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { LightGrayShieldInline } from '@ithemes/security-style-guide';

export const StyledSummary = styled.div`
	max-width: 900px;
	width: 100%;
	flex-grow: 1;
 	margin-top: ${ ( { isSmall } ) => isSmall && '100px' };
`;

export const StyledImprovementsList = styled.ul`
	list-style: disc inside;
	display: flex;
	flex-direction: column;
	gap: 0.75rem;
	margin: 0;
`;

export const StyledImprovement = styled.li`
	margin: 0 0.5rem;
`;

export const StyledFeatures = styled.div`
	margin-top: 3.5rem;
	padding: 1.5rem;
	border: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
	border-radius: 2px;
	box-shadow: 0 4px 8px 0 #00000033;
	background: no-repeat url("${ LightGrayShieldInline }");
	background-position: right -40px top -35px;
	background-size: 180px;

	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.small }px ) {
		background-position: right -50% center;
		background-size: 65%;
	}
`;

export const StyledFeaturesLayout = styled( Flex )`
	max-width: 700px;
`;

export const StyledFeaturesList = styled( List )`
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(225px, 1fr));
	gap: 1.25rem;
`;

export const StyledUpgradeButton = styled( Button )`
	align-self: start;
`;
