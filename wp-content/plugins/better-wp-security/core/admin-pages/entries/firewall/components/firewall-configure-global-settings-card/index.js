import { useCallback } from '@wordpress/element';
import { ModuleFormInputs, useSettingsForm } from '@ithemes/security.pages.settings';
import { PageHeader, Surface } from '@ithemes/ui';
import { StyledGlobalSettingsContainer } from './styles';

const allowedFields = [
	'user_lockout_message',
	'lockout_period',
	'blacklist_period',
	'blacklist',
	'blacklist_count',
	'lockout_message',
	'user_lockout_message',
	'community_lockout_message',
	'automatic_temp_auth',
	'lockout_white_list',
];

export default function FirewallGlobalSettingsCard( { module } ) {
	const _filterFields = useCallback(
		( value, key ) => allowedFields.includes( key ),
		[]
	);

	const {
		schema,
		uiSchema,
		formData,
		setFormData,
	} = useSettingsForm( module, _filterFields );

	return (
		<Surface>
			<PageHeader
				title={ module.title }
				description={ module.description }
				fullWidth
				hasBorder
			/>
			<StyledGlobalSettingsContainer>
				<ModuleFormInputs
					module={ module }
					schema={ schema }
					uiSchema={ uiSchema }
					formData={ formData }
					setFormData={ setFormData }
				/>
			</StyledGlobalSettingsContainer>
		</Surface>
	);
}
