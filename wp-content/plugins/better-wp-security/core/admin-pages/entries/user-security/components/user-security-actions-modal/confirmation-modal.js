/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	check as confirmChangesIcon,
	chevronLeft as backArrow,
} from '@wordpress/icons';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * SolidWP dependencies
 */
import {
	Button,
	TextSize,
	TextWeight,
	MessageList,
} from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { UserSecurityPillContainer } from './pill-container';

import { userSecurityStore } from '@ithemes/security.packages.data';
import {
	ConfirmationModalContainer,
	StyledUserSecurityConfirmModalBackButton,
	StyledUserSecurityConfirmModalChangesList,
	StyledUserSecurityConfirmModalConfirmButtonContainer,
	StyledSubheading,
	StyledEditListItem,
} from './styles';

export function UserSecurityActionsConfirmModal( {
	items,
	activeActions,
	setActiveActions,
	confirmationMessages,
	setConfirmationMessages,
} ) {
	const { currentlySelectedUsers, userSelectionType, isApplyingQuickActions, quickActionErrors } = useSelect( ( select ) => ( {
		currentlySelectedUsers: select( userSecurityStore ).getCurrentlySelectedUsers(),
		userSelectionType: select( userSecurityStore ).getUserSelectionType(),
		isApplyingQuickActions:
			select( userSecurityStore ).isApplyingQuickActions( 'main' ) ||
			select( userSecurityStore ).isApplyingQuickActions( 'bulk-select' ),
		quickActionErrors:
			select( userSecurityStore ).getQuickActionsError( 'main' ) ||
			select( userSecurityStore ).getQuickActionsError( 'bulk-select' ),
	} ), [] );
	const { openQuickEdit, applyQueryActionsToUsers, applyQuickActionsToQuery } = useDispatch( userSecurityStore );

	const onApply = async () => {
		let result = '';
		switch ( userSelectionType ) {
			case 'all':
				result = await applyQuickActionsToQuery(
					'main',
					userSelectionType,
					activeActions,
				);
				break;
			case 'window':
				result = await applyQueryActionsToUsers(
					currentlySelectedUsers,
					activeActions,
					'bulk-select',
				);
				break;
		}

		if ( result === null ) {
			setActiveActions( {} );
			setConfirmationMessages( {} );
		}
	};

	return (
		<ConfirmationModalContainer>
			<StyledUserSecurityConfirmModalBackButton
				variant="tertiary"
				text={ __( 'Back to Quick User Security Actions', 'better-wp-security' ) }
				onClick={ () => {
					openQuickEdit();
				} }
				icon={ backArrow }
			/>
			{ quickActionErrors && (
				<MessageList
					type="danger"
					messages={ quickActionErrors ? [ quickActionErrors.message || __( 'An unexpected error occurred.', 'better-wp-security' ) ] : [] }
				/>
			) }
			<UserSecurityPillContainer
				items={ items }
			/>
			<StyledSubheading
				text={ __( 'Are you sure you want to make these edits?', 'better-wp-security' ) }
				level={ 2 }
				weight={ TextWeight.HEAVY }
				size={ TextSize.NORMAL }
			/>
			<StyledUserSecurityConfirmModalChangesList>
				{
					Object.entries( confirmationMessages ).map( ( [ key, value ] ) => (
						<StyledEditListItem
							text={ value }
							icon={ confirmChangesIcon }
							textSize={ TextSize.NORMAL }
							key={ key }
						/>
					) )
				}
			</StyledUserSecurityConfirmModalChangesList>
			<StyledUserSecurityConfirmModalConfirmButtonContainer>
				<Button
					variant="primary"
					text={ __( 'Confirm Edits', 'better-wp-security' ) }
					onClick={ onApply }
					isBusy={ isApplyingQuickActions }
				/>
			</StyledUserSecurityConfirmModalConfirmButtonContainer>
		</ConfirmationModalContainer>
	);
}
