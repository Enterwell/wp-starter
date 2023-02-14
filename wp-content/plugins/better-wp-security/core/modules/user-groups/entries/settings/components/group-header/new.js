/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { GroupHeader } from './';

function NewGroupHeader( { label } ) {
	if ( ! label || ! label.length ) {
		label = __( 'New Group', 'better-wp-security' );
	}

	return (
		<GroupHeader label={ label } />
	);
}

export default compose( [
	withSelect( ( select ) => {
		return ( {
			label: select( 'ithemes-security/user-groups-editor' ).getEditedGroupAttribute( 'new', 'label' ),
		} );
	} ),
] )( NewGroupHeader );
