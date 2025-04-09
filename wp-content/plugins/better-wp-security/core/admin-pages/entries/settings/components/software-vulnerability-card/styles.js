/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * SolidWP dependencies
 */
import { Text } from '@ithemes/ui';

export const StyledVulnerabilityCard = styled.div`
	background: ${ ( { score } ) => getBackground( score ) };
	border: 1px solid ${ ( { score } ) => getBorder( score ) };
	border-radius: 0.25rem;
	padding: 1rem 1.5rem;
	align-items: stretch;
	display: flex;
	flex-wrap: wrap;
	
	& > * {
		flex-basis: 0;
		flex-grow: 1;
	}
`;

export const StyledVulnerabilityBadge = styled( Text )`
	padding: 0.125rem 0.75rem;
	background: ${ ( { score } ) => getBadge( score ) };
`;

function getBackground( score ) {
	if ( score < 7 ) {
		return '#fffbef';
	}

	return '#fcf0f1';
}

function getBorder( score ) {
	if ( score < 7 ) {
		return '#ffd65d';
	}

	return '#b32d2e';
}

function getBadge( score ) {
	if ( score < 7 ) {
		return '#f4c520';
	}

	return '#ff8085';
}
