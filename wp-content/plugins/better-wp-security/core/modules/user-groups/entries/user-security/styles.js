/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Modal } from '@wordpress/components';

/**
 * SolidWP dependencies
 */
import { Button, Text } from '@ithemes/ui';

export const StyledButtonsContainer = styled.div`
	display: flex;
	gap: 1rem;
`;

export const StyledUserSecurityUserGroupActionsModal = styled( Modal )`
	max-width: 766px;
	min-width: 480px;
	
	.components-modal__content {
		padding: 0;
	}
  
	.components-modal__header {
		padding: .5rem 1.5rem;
	}
  
	.components-modal__header-heading {
		font-size: 1rem;
	}
`;

export const StyledSettingsFormContainer = styled.div`
	padding: .5rem 1.5rem;
`;

export const StyledModalPillContainer = styled.div`
	display: flex;
	gap: 1rem;
	flex-direction: row;
	flex-wrap: wrap;
	padding: 1.25rem 0 2rem 0;
`;

export const StyledUserGroupPill = styled( Text )`
	background: ${ ( { theme } ) => theme.colors.surface.secondary };
	border-radius: 25px;
	padding: .5rem;
`;

export const StyledUserGroupsConfirmationButton = styled( Button )`
	margin: 1rem 0 1.5rem 0;
`;
