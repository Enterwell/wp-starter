/**
 * External Dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Dropdown, Icon } from '@wordpress/components';

/**
 * SolidWP dependencies
 */
import { Surface, Heading, Text, Button } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { CheckboxControl } from '@ithemes/security-components';

export const StyledTableSection = styled( Surface )`
	flex-shrink: 1;
	position: relative;
`;

export const StyledUserAvatar = styled.img`
	width: 2.5rem;
	border-radius: 50%;
`;

export const StyledStatusCheck = styled( Icon )`
	fill: white;
	background-color: #438C56;
	border-radius: 2rem;
`;

export const StyledStatusRedCircle = styled( Icon )`
	background-color: #FFABAF;
	border-radius: 2rem;
`;

export const StyledBulkEditTH = styled.th`
	display: grid;
	grid-template-columns: 0fr 0fr 1fr;
	align-items: center;
	gap: ${ ( { theme: { getSize } } ) => getSize( .25 ) };
	padding-top: ${ ( { theme: { getSize } } ) => getSize( .25 ) } !important;
	padding-bottom: ${ ( { theme: { getSize } } ) => getSize( .25 ) } !important;
`;

export const StyledUser = styled.div`
	display: grid;
	grid-template-columns: 0fr 0fr 1fr;
	align-items: center;
	gap: ${ ( { theme: { getSize } } ) => getSize( .5 ) };
	padding-top: ${ ( { theme: { getSize } } ) => getSize( .75 ) } !important;
	padding-bottom: ${ ( { theme: { getSize } } ) => getSize( .75 ) } !important;
`;

export const StyledCheckboxControl = styled( CheckboxControl )`
	.components-base-control__field {
		margin-bottom: 0;
		
		span {
			margin-right: 0;
		}
	}
`;

export const StyledDropdown = styled( Dropdown )`
	justify-content: center;
	display: flex;
`;

export const StyledHeadingUserCheckboxOption = styled( Heading )`
	white-space: nowrap;
`;

export const StyledTextUserCheckboxOption = styled( Text )`
	white-space: nowrap;
`;

export const StyledUserCheckboxDropdownContent = styled( Surface )`
	padding: ${ ( { theme: { getSize } } ) =>
		`${ getSize( .2 ) } ${ getSize( .4 ) }` };
	display: flex;
	gap: ${ ( { theme: { getSize } } ) => getSize( .6 ) };
	flex-direction: column;
`;

export const StyledUserTableDropdown = styled( Dropdown )`
	justify-content: center;
	display: flex;
`;

export const StyledUserTH = styled( Text )`
`;

export const StyledButton = styled( Button )`
	font-weight: 600;
	text-transform: capitalize;
`;

export const StyledCaretDropdownButton = styled( Button )`
	padding: 0 !important;
	min-width: 24px !important;
`;

export const StyledRowDetailsContainer = styled( Surface )`
	display: ${ ( { isExpanded } ) => isExpanded ? 'table-row' : 'none' }; 
`;

export const StyledUserDetailsCaption = styled( Text )`
	text-align: left;
	color: #858585;
`;

export const StyledUserDetailTR = styled.tr`
	
`;

export const StyledUserDetailText = styled( Text )`
	text-align: right;
	justify-content: right;
	padding: 0.4rem 2.5rem 0.4rem 0;
	border-spacing: 0;
	border-bottom: 0;
`;

export const StyledTable = styled.table`
	width: 100%;
	border-collapse: collapse;
	border-spacing: 0;
	border-right: 2px solid #d9d9d9 !important;
`;

export const StyledUserDetailTableBody = styled.tbody`
	padding: 0 2.5rem;
`;

export const StyledUserDetailTH = styled( Text )`
	border-bottom: none;
	padding: 0.4rem 2.5rem 0.4rem 0 !important;
`;

export const StyledUserDetailTDContainer = styled.td`
	padding: 1rem 2.5rem !important;
`;
