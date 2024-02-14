/**
 * External dependencies
 */
import { noop } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { createContext, useMemo } from '@wordpress/element';
import { createSlotFill } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';

/**
 * SolidWP dependencies
 */
import {
	TextWeight,
	TextVariant,
	Button,
	TextSize,
} from '@ithemes/ui';

/**
 * Internal dependencies
 */
import {
	StyledQuickUserActionsHeading,
	StyledModalContainer,
	StyledModalChangesMadeSection,
	StyledChangesMadeText,
	StyledUserSecurityModalSubmitButtonContainer,
} from './styles';
import {
	UserSecurityActionsModalChangesMade,
} from './changes-made-section';
import {
	EditingModalActionButton,
} from './modal-action-section';
import { UserSecurityPillContainer } from './pill-container';
import { userSecurityStore } from '@ithemes/security.packages.data';

export const UserSecurityActionsContext = createContext( {
	activeActions: {},
	setActiveActions: noop,
	confirmationMessages: {},
	setConfirmationMessages: noop,
} );

const { Slot: EditingModalActionSlot, Fill: EditingModalActionFill } = createSlotFill( 'editingModalActions' );
export { EditingModalActionFill };

export function UserSecurityActionsEditingModal( {
	setActiveActions,
	activeActions,
	confirmationMessages,
	setConfirmationMessages,
} ) {
	const contextValue = useMemo( () => ( {
		activeActions,
		setActiveActions,
		confirmationMessages,
		setConfirmationMessages,
	} ), [
		activeActions,
		setActiveActions,
		confirmationMessages,
		setConfirmationMessages,
	] );

	const { confirmQuickEdit } = useDispatch( userSecurityStore );
	const onApply = () => {
		confirmQuickEdit();
	};

	return (
		<UserSecurityActionsContext.Provider value={ contextValue }>
			<UserSecurityPillContainer />
			<StyledQuickUserActionsHeading
				level={ 2 }
				weight={ TextWeight.HEAVY }
				text={ __( 'Quick User Actions', 'LION ' ) }
				size={ TextSize.LARGE }
			/>
			<StyledModalContainer>
				<EditingModalActionSlot />
				<EditingModalActionButton
					title={ __( 'Force Users to Reset Passwords', 'better-wp-security' ) }
					description={
						__( 'Change passwords immediately following a breach where user accounts may have been compromised.', 'better-wp-security' )
					}
					buttonText={ __( 'Force a Password Reset', 'better-wp-security' ) }
					slug="reset-password"
					confirmationText={ __( 'Sending Password Reset', 'better-wp-security' ) }
				/>
				<EditingModalActionButton
					title={ __( 'Delete users\' account', 'better-wp-security' ) }
					description={ __( 'Remove unused user accounts.', 'better-wp-security' ) }
					buttonText={ __( 'Delete Accounts', 'better-wp-security' ) }
					slug="delete"
					isDestructive
					confirmationText={ __( 'Deleting Accounts', 'better-wp-security' ) }
				/>
				<EditingModalActionButton
					title={ __( 'Force a User Lockout', 'better-wp-security' ) }
					description={ __( 'Use this option to terminate an active user session immediately.', 'better-wp-security' ) }
					buttonText={ __( 'Force User Logout', 'better-wp-security' ) }
					slug="force-logout"
					isDestructive
					confirmationText={ __( 'Forcing Logout', 'better-wp-security' ) }
				/>
			</StyledModalContainer>
			<StyledModalChangesMadeSection
				variant="secondary"
			>
				<StyledChangesMadeText
					as="p"
					text={ __( 'Selected changes', 'better-wp-security' ) }
					variant={ TextVariant.MUTED }
				/>
				<UserSecurityActionsModalChangesMade
					setActiveActions={ setActiveActions }
					actions={ activeActions }
					confirmationMessages={ confirmationMessages }
					setConfirmationMessages={ setConfirmationMessages }
				/>
			</StyledModalChangesMadeSection>
			<StyledUserSecurityModalSubmitButtonContainer>
				<Button
					text={ __( 'Review Changes', 'better-wp-security' ) }
					variant="primary"
					onClick={ onApply }
				/>
			</StyledUserSecurityModalSubmitButtonContainer>
		</UserSecurityActionsContext.Provider>
	);
}
