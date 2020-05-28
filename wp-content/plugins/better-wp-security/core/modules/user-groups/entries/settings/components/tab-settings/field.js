/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { ToggleControl } from '@wordpress/components';

function Field( { schema, value, edit, disabled = false } ) {
	return (
		<ToggleControl
			checked={ value === true }
			label={ schema.title }
			help={ schema.description }
			disabled={ disabled }
			onChange={ ( checked ) => edit( checked ) } />
	);
}

export default compose( [
	withSelect( ( select, { groupId, module, setting } ) => ( {
		value: select( 'ithemes-security/user-groups-editor' ).getEditedGroupSetting( groupId, module, setting ),
	} ) ),
	withDispatch( ( dispatch, { groupId, module, setting } ) => ( {
		edit( value ) {
			return dispatch( 'ithemes-security/user-groups-editor' ).editGroupSetting( groupId, module, setting, value );
		},
	} ) ),
] )( Field );

