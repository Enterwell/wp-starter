/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * iThemes dependencies
 */
import { Surface, Text } from '@ithemes/ui';

export function severityColor( severity ) {
	switch ( severity ) {
		case 'low':
			return '#B8E6BF';
		case 'medium':
			return '#FFC518';
		case 'high':
			return '#FFABAF';
		default:
			return '#D63638';
	}
}

export function severityText( severity ) {
	switch ( severity ) {
		case 'low':
			return __( 'Low', 'better-wp-security' );
		case 'medium':
			return __( 'Medium', 'better-wp-security' );
		case 'high':
			return __( 'High', 'better-wp-security' );
		default:
			return __( 'Critical', 'better-wp-security' );
	}
}

export const StyledScanInfo = styled.div`
	display: grid;
	grid-column-gap: 2rem;
	grid-template-columns: 0.5fr 1fr;
	overflow-wrap: anywhere;
`;

export const StyledSeverity = styled( Text )`
	display: flex;
	justify-content: center;
	align-items: center;
	padding: 0.125rem 0.5rem;
	width: min-content;
	min-width: 4.5rem;
	background-color: ${ ( { backgroundColor } ) => backgroundColor };
	border-radius: 2px;
`;

export const StyledDetailsContainer = styled.div`
	display: flex;
	gap: 2rem;
	flex-wrap: wrap;
	justify-content: space-between;
`;

export const StyledRowDetailsContainer = styled( Surface )`
	display: ${ ( { isExpanded } ) => isExpanded ? 'table-row' : 'none' };
`;

export const StyledDetailContent = styled.div`
	display: flex;
	flex-wrap: wrap;
	gap: 2rem;
`;

export const StyledDetailColumn = styled.div`
	display: flex;
	flex-direction: column;
	justify-content: space-between;
	max-width: 70ch;
`;

export const StyledScanIssueText = styled( Text )`
	line-height: 1.3rem;
	margin-top: .4rem;
`;

// tablet layout
export const StyledCombinedColumns = styled.div`
	display: flex;
	flex-direction: column;
	gap: 0.25rem;
`;

export const StyledAction = styled.td`
	text-align: right;
`;

// Mobile list styles

export const StyledListItem = styled.div`
	display: grid;
	grid-template-columns: 2fr 1fr 0.5fr;
	gap: 1rem;
	overflow-wrap: anywhere;
	align-items: center;
	padding: 1rem;
`;

export const StyledListDetailsContainer = styled( Surface )`
	display: ${ ( { isExpanded } ) => ! isExpanded && 'none' };
	padding: 1rem;
`;
