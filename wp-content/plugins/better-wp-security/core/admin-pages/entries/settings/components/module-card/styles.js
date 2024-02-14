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
import { Text } from '@ithemes/ui';

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
