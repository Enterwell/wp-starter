/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { Disabled } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Solid dependencies
 */
import { Button, useConfirmationDialog } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { store as userGroupsStore } from '@ithemes/security.user-groups.api';
import { store as uiStore } from '@ithemes/security.user-groups.ui';
import { coreStore } from '@ithemes/security.packages.data';
import { EditGroupFields, PageHeaderActionFill } from '../';

export default function TabEditGroup( { groupId, children } ) {
	const { label, isLoading } = useSelect( ( select ) => ( {
		label: select( uiStore ).getEditedGroupAttribute( groupId, 'label' ),
		isLoading:
			select( 'core/data' ).isResolving(
				userGroupsStore,
				'getGroup',
				[ groupId ]
			) ||
			select( 'core/data' ).isResolving(
				coreStore,
				'getIndex'
			),
	} ), [ groupId ] );
	const { deleteGroup } = useDispatch( uiStore );

	const [ onDelete, confirmationDialog ] = useConfirmationDialog( {
		onContinue: () => deleteGroup( groupId ),
		title: __( 'Confirm your action', 'better-wp-security' ),
		body: sprintf(
			/* translators: 1. User Group name. */
			__( 'Are you sure you want to delete the “%s” user group?', 'better-wp-security' ),
			label ?? __( 'Untitled', 'better-wp-security' )
		),
		continueText: __( 'Delete User Group', 'better-wp-security' ),
	} );

	return (
		<>
			{ confirmationDialog }
			<PageHeaderActionFill>
				<Button
					text={ __( 'Delete User Group', 'better-wp-security' ) }
					variant="tertiary"
					isDestructive
					onClick={ onDelete }
				/>
			</PageHeaderActionFill>
			<Disabled isDisabled={ isLoading }>
				{ children }
				<EditGroupFields groupId={ groupId } disabled={ isLoading } />
			</Disabled>
		</>
	);
}
