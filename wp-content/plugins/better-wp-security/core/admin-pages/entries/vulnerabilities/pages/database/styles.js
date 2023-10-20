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

export const StyledTableSection = styled( Surface )`
	flex-shrink: 1;
	position: relative;
`;

export const StyledVulnerabilityName = styled( Text )`
	grid-area: name;
`;

export const StyledVulnerabilityVersion = styled( Text )`
	grid-area: version
`;

export const StyledVulnerabilityDetail = styled( Text )`
	grid-area: detail;
`;

export const StyledNoWrapCell = styled.td`
	white-space: nowrap;
`;

export const StyledVulnerability = styled( Surface )`
	display: grid;
	grid-template-columns: 1fr 0.5fr 1fr;
	grid-template-areas: "name name name" "version detail detail";
	align-items: center;
	@media screen and (min-width: 960px) {
		grid-template-columns: 1fr 0.5fr 1fr;
		grid-template-areas: "name version detail";
	  	gap: ${ ( { theme: { getSize } } ) => getSize( 1 ) };
	}
`;

export const StyledSeverity = styled( Text )`
	padding: 1.5px 6.5px;
	background-color: ${ ( { backgroundColor } ) => backgroundColor };
	border-radius: 2px;
	width:35px;
	display: flex;
	justify-content: center;
`;

export const StyledStatusCheck = styled( Icon )`
  // the fill colors are in the component, otherwise black - or could use !important
	fill: white;
	background-color: #438C56;
	border-radius: 2rem;
`;

export const StyledStatusRedCircle = styled( Icon )`
	background-color: #FFABAF;
	border-radius: 2rem;
`;

export const StyledHeader = styled.header`
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 1rem 1.25rem;
`;

export const StyledDatabaseWarning = styled( Surface )`
	display: block;
	align-items: center;
	justify-content: space-between;
	padding: ${ ( { theme: { getSize } } ) =>
		`${ getSize( .5 ) } ${ getSize( 1.5 ) }` };
	text-align: center;
`;
