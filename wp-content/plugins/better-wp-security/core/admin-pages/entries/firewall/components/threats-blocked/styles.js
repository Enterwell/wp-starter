/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Card, CardHeader } from '@wordpress/components';

/**
 * SolidWP dependencies
 */
import { Notice, Surface } from '@ithemes/ui';

export const StyledLineGraphCard = styled( Card )`
	display: flex;
	flex-direction: column;
	& circle {
		fill: ${ ( { theme } ) => theme.colors.surface.primaryAccent };
		fill-opacity: 1;
	}
`;

export const StyledCardHeader = styled( CardHeader )`
	padding: 1rem 1.25rem;
`;

export const StyledTooltip = styled( Surface )`
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	padding: 4px 8px;
	border-radius: 2px;
`;

export const NoResultsContainer = styled( Surface )`
	height: 275px;
`;

export const StyledNotice = styled( Notice )`
	margin: 1rem;
`;

export const StyledEmptyStateContainer = styled.div`
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	height: 275px;
	gap: 1rem;
	padding: 2rem;
`;
