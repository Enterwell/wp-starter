/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Icon } from '@wordpress/components';

/**
 * Solid dependencies
 */
import { Surface, PageHeader, Text } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { ErrorList, PrimarySchemaFormInputs, TabbedNavigation } from '@ithemes/security-ui';

export const StyledPageHeader = styled( PageHeader )`
	margin-bottom: 1rem;
`;

export const StyledFormContainer = styled( Surface )`
	border: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
`;

export const StyledErrorList = styled( ErrorList )`
	margin: 0 1.5rem 1rem;
`;

export const StyledSingleModuleSettingsContainer = styled.form`
	padding: 1rem 0;
`;

export const StyledPrimarySchemaFormInputs = styled( PrimarySchemaFormInputs )`
	& .itsec-rjsf-object-fieldset > .form-group,
	& .itsec-rjsf-object-fieldset > .itsec-rjsf-section-title {
		padding: 0 1.5rem;
	}
`;

export const StyledSettingsActions = styled.div`
	display: flex;
	align-items: center;
	justify-content: end;
	gap: 1.5rem;
	margin-top: 2rem;
`;

export const StyledModuleList = styled.form`
	padding: 1.25rem 1.5rem;
	display: flex;
	flex-direction: column;
	gap: 1.25rem;
`;

export const StyledModulePanel = styled.section`
	border: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
	box-shadow: ${ ( { theme, isHighlighted } ) => isHighlighted && `0 0 0 var(--wp-admin-border-width-focus) ${ theme.colors.primary.base }` };
`;

export const StyledModulePanelHeader = styled.header`
	padding: 1.25rem 1rem;
	border-bottom: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
	display: flex;
	align-items: center;
	justify-content: space-between;
`;

export const StyledModulePanelNoSettingsDescription = styled.div`
	padding: 1rem;
`;

export const StyledModulePanelTrigger = styled.button`
	display: grid;
	grid-template-areas: "title graphic" "description graphic";
	grid-template-columns: auto min-content;
	align-items: center;
	gap: 0.5rem;
	width: 100%;
	padding: 1rem;
	cursor: pointer;
	background-color: transparent;
	border-width: 0;
  
	&[disabled] {
		cursor: default;
	}
`;

export const StyledModulePanelTitle = styled( Text )`
	grid-area: title;
`;

export const StyledModulePanelDescription = styled( Text )`
	grid-area: description;
`;

export const StyledModulePanelIcon = styled( Icon )`
	grid-area: graphic;
`;

export const StyledModulePanelBody = styled.div`
	display: ${ ( { isOpen } ) => isOpen ? 'block' : 'none' };
	padding: 1rem 0; 
	background: #f6f7f7;
`;

export const StyledModulePanelNotices = styled.div`
	padding: 0 1rem 1rem;
`;

export const StyledNavigation = styled( TabbedNavigation )`
	padding: 0 1.5rem;
`;

export const StyledOnboardWrapper = styled.div`
	flex-grow: 1;
	max-width: 830px;
	width: 100%;
`;
