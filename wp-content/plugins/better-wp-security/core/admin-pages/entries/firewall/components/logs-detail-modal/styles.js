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
import { Text } from '@ithemes/ui';

export const StyledModal = styled( Modal )`
	width: 60%;
	max-width: 718px;
	.components-modal__header {
		padding: 0.5rem 1.5rem;
	}
	.components-modal__header-heading {
		font-size: 1rem;
	}
	.components-modal__content {
		padding: 0;
	}
	& button:hover {
		color: ${ ( { theme } ) => theme.colors.secondary.darker20 };
	}
	& button:focus {
		box-shadow: 0 0 0 2px ${ ( { theme } ) => theme.colors.primary.base } !important;
	}
`;

export const StyledModalBody = styled.div`
	display: flex;
	flex-direction: column;
	gap: 2rem;
	padding: 1.5rem 1.5rem 3.5rem;
`;

export const StyledColumnContainer = styled.div`
	display: grid;
	grid-template-columns: 1fr 1fr;
	margin-bottom: 2rem;
`;

export const StyledColumn = styled.div`
	display: flex;
	flex-direction: column;
	gap: 0.75rem;
	padding-top: 0.5rem;
	padding-bottom: 1rem;
	:first-child {
		border-right: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
		padding-right: 3rem;
	}
	:nth-child(2) {
		margin-left: 3rem;
	}
`;

export const StyledRow = styled.div`
	display: grid;
	grid-template-columns: 0.175fr 1fr;
	gap: 1rem;
	
	:nth-child(even) {
		border-bottom: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
		padding-bottom: 2rem;
	}

	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.xlarge }px) {
		align-items: center;
	}
`;

export const StyledRowContent = styled.div`
	display: flex;
	gap: 1rem;
`;

export const StyledRequestMethod = styled( Text )`
	color: #D63638;
`;
