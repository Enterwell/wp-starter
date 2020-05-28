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
import { GroupHeader } from './';

function SingleGroupHeader( { type, label, isDeleting, deleteGroup } ) {
	const canDelete = type === 'user-group';

	if ( ! label || ! label.length ) {
		label = __( 'Untitled', 'better-wp-security' );
	}

	return (
		<GroupHeader label={ label }>
			{ canDelete && (
				<Button onClick={ deleteGroup } isBusy={ isDeleting } isLink isDestructive>
					{ __( 'Delete Group', 'better-wp-security' ) }
				</Button>
			) }
		</GroupHeader>
	);
}

export default compose( [
	withSelect( ( select, { groupId } ) => {
		const type = select( 'ithemes-security/user-groups' ).getMatchableType( groupId );
		const isDeleting = type === 'user-group' ? select( 'ithemes-security/user-groups' ).isDeleting( groupId ) : false;

		let label;

		if ( type === 'user-group' ) {
			label = select( 'ithemes-security/user-groups-editor' ).getEditedGroupAttribute( groupId, 'label' );
		}

		if ( label === undefined ) {
			label = select( 'ithemes-security/user-groups' ).getMatchableLabel( groupId );
		}

		return ( {
			type,
			label,
			isDeleting,
		} );
	} ),
	withDispatch( ( dispatch, { groupId } ) => ( {
		deleteGroup() {
			return dispatch( 'ithemes-security/user-groups' ).deleteGroup( groupId );
		},
	} ) ),
] )( SingleGroupHeader );
