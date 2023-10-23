/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Dropdown, Modal } from '@wordpress/components';

/**
 * SolidWP dependencies
 */
import {
	Text,
	Heading,
	Button,
	Surface,
	List,
	ListItem,
} from '@ithemes/ui';

export const StyledUserSecurityActionsModal = styled( Modal )`
	max-width: 766px;
	min-width: 480px;
	
	.components-modal__content {
		padding: 0;
	}
  
	.components-modal__header {
		padding: ${ ( { theme: { getSize } } ) =>
		`${ getSize( .5 ) } ${ getSize( 1.5 ) }` };
		& button:hover {
			color: ${ ( { theme } ) => theme.colors.secondary.darker20 };
		}
		& button:focus {
			box-shadow: 0 0 0 2px ${ ( { theme } ) => theme.colors.primary.base };
		}
	}
  
	.components-modal__header-heading {
		font-size: ${ ( { theme: { getSize } } ) => getSize( 1 ) };
	}
`;

export const ConfirmationModalContainer = styled.div`
	display: flex;
	flex-direction: column;
	gap: ${ ( { theme: { getSize } } ) => getSize( 1 ) };
`;

export const StyledUserSecurityConfirmModalBackButton = styled( Button )`
	align-self: flex-start;
	padding-left: 1rem !important; 
`;

export const StyledUserSecurityConfirmModalChangesList = styled( List )`
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: ${ ( { theme: { getSize } } ) => getSize( 1 ) };`;

export const StyledSubheading = styled( Heading )`
	padding: 0.5rem 1.5rem;
`;

export const StyledModalPillContainer = styled.div`
	display: flex;
	gap: ${ ( { theme: { getSize } } ) => getSize( 1 ) };
	flex-direction: row;
	flex-wrap: wrap;
	padding: ${ ( { theme: { getSize } } ) =>
		`${ getSize( 1 ) } ${ getSize( 1.5 ) }` };
`;

export const StyledUserPill = styled( Text )`
	background: #e7e7e7;
	border-radius: 25px;
	padding: ${ ( { theme: { getSize } } ) => getSize( .5 ) };
`;

export const StyledQuickUserActionsHeading = styled( Heading )`
	padding: ${ ( { theme: { getSize } } ) =>
		`${ getSize( .5 ) } ${ getSize( 1.5 ) }` };
`;

export const StyledEditListItem = styled( ListItem )`
	padding-left: 1.5rem !important;
`;

export const StyledModalContainer = styled.div`
	display: grid;
	grid-template-columns: repeat( auto-fit, minmax( 240px, 1fr ) );
	padding: ${ ( { theme: { getSize } } ) =>
		`${ getSize( 1 ) } ${ getSize( 1.5 ) } ${ getSize( 1.2 ) } ${ getSize( 1.5 ) }` };
	gap: ${ ( { theme: { getSize } } ) => getSize( 2 ) };
`;

export const StyledModalActionSection = styled.div`
	display: flex;
	flex-direction: column;
	gap: ${ ( { theme: { getSize } } ) => getSize( .75 ) };
`;

export const StyledModalActionSectionButton = styled( Button )`
	align-self: flex-start;
	margin-top: auto;
`;

export const StyledModalChangesMadeSection = styled( Surface )`
	padding: ${ ( { theme: { getSize } } ) =>
		`${ getSize( .5 ) } ${ getSize( 1.5 ) }` };
	background: #f6f7f7 !important;
`;

export const StyledChangesMadeText = styled( Text )`
	padding: ${ ( { theme: { getSize } } ) =>
		`${ getSize( 1 ) } 0` };
`;

export const StyledButton = styled( Button )`
	font-weight: 600;
`;

export const StyledCaretDropdownButton = styled( Button )`
	min-width: 24px !important;
	border: 1px solid #757575;
`;

export const StyledDropdown = styled( Dropdown )`
	display: flex;

	.components-dropdown__content .components-popover__content {
		padding: 0 !important;
	}
`;

export const StyledHeadingUserCheckboxOption = styled( Heading )`
	white-space: nowrap;
	padding: ${ ( { theme: { getSize } } ) =>
		`${ getSize( .5 ) } ${ getSize( 1 ) }` };
`;

export const StyledTextUserCheckboxOption = styled( Text )`
	white-space: nowrap;
`;

export const StyledUserCheckboxDropdownContent = styled( Surface )`
	display: flex;
	flex-direction: column;
	min-width: 280px;
`;

export const StyledUserSecurityModalDropdownOverlay = styled( Surface )`
	padding: ${ ( { theme: { getSize } } ) =>
		`${ getSize( .5 ) } ${ getSize( 1 ) }` };
	background: #F6F7F7 !important;
	max-height: 180px;
	overflow-y: auto;
`;

export const StyledUserSecurityModalAddButton = styled( Button )`
	background: black !important;
	color: #fff !important;
	align-self: center;
	margin: 1rem;
`;

export const StyledUserSecurityModalSubmitButtonContainer = styled.div`
	padding: ${ ( { theme: { getSize } } ) =>
		`${ getSize( 1 ) } ${ getSize( 1.5 ) }` };
`;

export const StyledUserSecurityConfirmModalChangesContainer = styled.div`
	display: grid;
	grid-template-columns: 1fr 1fr;
	padding: ${ ( { theme: { getSize } } ) =>
		`${ getSize( .5 ) } ${ getSize( 1.5 ) }` };
	gap: ${ ( { theme: { getSize } } ) => getSize( 1 ) };
`;

export const StyledUserSecurityConfirmModalConfirmButtonContainer = styled.div`
  padding: ${ ( { theme: { getSize } } ) =>
		`${ getSize( 1 ) } ${ getSize( 1.5 ) }` };
`;

export const StyledAdditionalUsersSelectedText = styled( Text )`
	align-self: flex-end;
`;
