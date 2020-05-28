/**
 * WordPress Dependencies
 */
import { TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import './style.scss';

function GroupLabel( { label, edit, disabled = false } ) {
	return (
		<TextControl
			label={ __( 'Group Name', 'better-wp-security' ) }
			value={ label }
			maxLength={ 50 }
			disabled={ disabled }
			onChange={ ( newLabel ) => edit( { label: newLabel } ) } />
	);
}

export default compose( [
	withSelect( ( select, { groupId } ) => ( {
		label: select( 'ithemes-security/user-groups-editor' ).getEditedGroupAttribute( groupId, 'label' ) || '',
	} ) ),
	withDispatch( ( dispatch, { groupId } ) => ( {
		edit( edit ) {
			return dispatch( 'ithemes-security/user-groups-editor' ).editGroup( groupId, edit );
		},
	} ) ),
] )( GroupLabel );
