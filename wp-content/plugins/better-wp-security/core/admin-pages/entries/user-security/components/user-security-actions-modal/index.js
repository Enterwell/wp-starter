/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { UserSecurityActionsEditingModal } from './editing-modal';
import { UserSecurityActionsConfirmModal } from './confirmation-modal';
import { StyledUserSecurityActionsModal } from './styles.js';
import { userSecurityStore } from '@ithemes/security.packages.data';

export function UserSecurityActionsModal() {
	const [ activeActions, setActiveActions ] = useState( {} );
	const [ confirmationMessages, setConfirmationMessages ] = useState( {} );
	const { selectedUsers, quickEditState } = useSelect(
		( select ) => ( {
			selectedUsers: select( userSecurityStore ).getCurrentlySelectedUsers(),
			quickEditState: select( userSecurityStore ).getQuickEditState(),
		} ),
		[]
	);
	const { closeQuickEdit } = useDispatch( userSecurityStore );

	return (
		<>
			{ quickEditState && (
				<StyledUserSecurityActionsModal
					title={
						quickEditState === true ? __( 'Quick User Security Actions', 'better-wp-security' ) : __( 'Quick User Security Actions - Confirm Actions', 'better-wp-security' )
					}
					onRequestClose={ closeQuickEdit }
				>
					{ quickEditState !== 'confirm' && (
						<UserSecurityActionsEditingModal
							setActiveActions={ setActiveActions }
							activeActions={ activeActions }
							confirmationMessages={ confirmationMessages }
							setConfirmationMessages={ setConfirmationMessages }
						/>
					) }
					{ quickEditState === 'confirm' && (
						<UserSecurityActionsConfirmModal
							activeActions={ activeActions }
							setActiveActions={ setActiveActions }
							items={ selectedUsers }
							confirmationMessages={ confirmationMessages }
							setConfirmationMessages={ setConfirmationMessages }
						/>
					) }
				</StyledUserSecurityActionsModal>
			) }

		</>
	);
}
