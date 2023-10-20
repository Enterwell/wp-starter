/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { CheckboxControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import SettingField from '../setting-field';
import store from '../../store';

export default function Field( { groupIds, module, setting, definition, disabled = false } ) {
	const { value } = useSelect( ( select ) => ( {
		value: select( store ).getBulkSettingValue( groupIds, module, setting ),
	} ), [ groupIds, module, setting ] );
	const { bulkEditGroupSetting } = useDispatch( store );

	return (
		<SettingField definition={ definition }>
			<CheckboxControl
				checked={ value === true }
				indeterminate={ value === null || value === undefined }
				label={ definition.title }
				disabled={ disabled }
				onChange={ ( checked ) => bulkEditGroupSetting( module, setting, checked ) }
				__nextHasNoMarginBottom
			/>
		</SettingField>
	);
}
