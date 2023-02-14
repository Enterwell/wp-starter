/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { EditGroupFields, TabBody } from '../';

function TabCreateGroup( { hasEdits, save, isSaving } ) {
	return (
		<TabBody name="create-group">
			<EditGroupFields groupId="new" />
			<TabBody.Row name="save">
				<Button disabled={ ! hasEdits } isPrimary onClick={ save } isBusy={ isSaving }>
					{ __( 'Create', 'better-wp-security' ) }
				</Button>
			</TabBody.Row>
		</TabBody>
	);
}

export default compose( [
	withSelect( ( select ) => ( {
		hasEdits: select( 'ithemes-security/user-groups-editor' ).hasEdits( 'new' ),
		isSaving: select( 'ithemes-security/user-groups-editor' ).isCreating( 'new' ),
	} ) ),
	withDispatch( ( dispatch ) => ( {
		save() {
			dispatch( 'ithemes-security/user-groups-editor' ).createGroup();
		},
	} ) ),
] )( TabCreateGroup );
