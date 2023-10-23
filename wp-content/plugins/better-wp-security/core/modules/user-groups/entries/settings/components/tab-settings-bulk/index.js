/**
 * WordPress dependencies
 */
import { Disabled } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { useSettingsDefinitions, SettingsForm, BulkSettingField } from '@ithemes/security.user-groups.ui';

export default function TabSettingsBulk( { groupIds, children } ) {
	const settings = useSettingsDefinitions();
	return (
		<Disabled isDisabled={ ! groupIds.length }>
			{ children }
			<SettingsForm
				definitions={ settings }
				settingComponent={ BulkSettingField }
				groupIds={ groupIds }
			/>
		</Disabled>
	);
}
