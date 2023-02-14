/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { CheckboxControl } from '@ithemes/security-components';

function Field( { schema, value, edit, disabled = false } ) {
	return (
		<CheckboxControl
			checked={ value === true }
			indeterminate={ value === null || value === undefined }
			label={ schema.title }
			help={ schema.description }
			disabled={ disabled }
			onChange={ ( checked ) => edit( checked ) } />
	);
}

export default compose( [
	withSelect( ( select, { module, setting, groupIds } ) => ( {
		value: select( 'ithemes-security/user-groups-editor' ).getBulkSettingValue( groupIds, module, setting ),
	} ) ),
	withDispatch( ( dispatch, { module, setting } ) => ( {
		edit( value ) {
			return dispatch( 'ithemes-security/user-groups-editor' ).bulkEditGroupSetting( module, setting, value );
		},
	} ) ),
] )( Field );
