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

function TabEditGroup( { groupId, hasEdits, save, isSaving, isLoading } ) {
	return (
		<TabBody name="edit-group" isLoading={ isLoading }>
			<EditGroupFields groupId={ groupId } disabled={ isLoading } />
			<TabBody.Row name="save">
				<Button disabled={ ! hasEdits } isPrimary onClick={ save } isBusy={ isSaving }>
					{ __( 'Save', 'better-wp-security' ) }
				</Button>
			</TabBody.Row>
		</TabBody>
	);
}

export default compose( [
	withSelect( ( select, { groupId } ) => ( {
		isLoading: select( 'core/data' ).isResolving( 'ithemes-security/user-groups', 'getGroup', [ groupId ] ) ||
			select( 'core/data' ).isResolving( 'ithemes-security/core', 'getIndex' ),
		hasEdits: select( 'ithemes-security/user-groups-editor' ).hasEdits( groupId ),
		isSaving: select( 'ithemes-security/user-groups' ).isUpdating( groupId ),
	} ) ),
	withDispatch( ( dispatch, { groupId } ) => ( {
		save() {
			return dispatch( 'ithemes-security/user-groups-editor' ).saveGroup( groupId );
		},
	} ) ),
] )( TabEditGroup );
