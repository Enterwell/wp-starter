/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { useCallback } from '@wordpress/element';
import { FlexBlock } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useInstanceId } from '@wordpress/compose';

/**
 * SolidWP dependencies
 */
import { Button, PageHeader, Surface } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import { ModuleFormInputs, useSettingsForm } from '@ithemes/security.pages.settings';
import { StyledGlobalSettingsContainer, StyledSettingsActions } from './styles';

/**
 * Settings Fields that are allowed for this module
 *
 * @type {string[]}
 */
const allowedFields = [
	'automatic_temp_auth',
	'lockout_white_list',
];

export default function AuthorizeIPs() {
	const { module, config } = useSelect( ( select ) => ( {
		module: select( MODULES_STORE_NAME ).getModule( 'global' ),
		config: select( MODULES_STORE_NAME ).getEditedModule( 'global' ),
	} ), [] );
	const { saveSettings } = useDispatch( MODULES_STORE_NAME );
	const _filterFields = useCallback(
		( value, key ) => allowedFields.includes( key ),
		[]
	);
	const id = useInstanceId(
		AuthorizeIPs,
		'itsec-ip-management-authorize-ips'
	);

	const {
		schema,
		uiSchema,
		formData,
		setFormData,
	} = useSettingsForm( module, _filterFields );

	if ( ! config ) {
		return null;
	}

	const onSave = ( e ) => {
		e.preventDefault();
		saveSettings( config.id, true );
	};

	return (
		<FlexBlock>
			<Surface>
				<PageHeader
					title={ __( 'Authorized IPs', 'better-wp-security' ) }
					description={ __( 'Add or remove authorized IPs.', 'better-wp-security' ) }
					fullWidth
					hasBorder
				/>
				<StyledGlobalSettingsContainer onSubmit={ onSave } id={ id }>
					<ModuleFormInputs
						module={ module }
						schema={ schema }
						uiSchema={ uiSchema }
						formData={ formData }
						setFormData={ setFormData }
					/>
					<SettingsActions moduleId={ module.id } form={ id } />
				</StyledGlobalSettingsContainer>
			</Surface>
		</FlexBlock>
	);
}

function SettingsActions( { moduleId, form } ) {
	const { isSaving, isDirty } = useSelect( ( select ) => ( {
		isDirty: select( MODULES_STORE_NAME ).getDirtySettings(),
		isSaving: select( MODULES_STORE_NAME ).isSavingSettings( moduleId ),
	} ), [ moduleId ] );
	const { resetSettingEdits } = useDispatch( MODULES_STORE_NAME );

	return (
		<StyledSettingsActions>
			<Button
				text={ __( 'Undo Changes', 'better-wp-security' ) }
				variant="secondary"
				onClick={ () => resetSettingEdits( moduleId ) }
				disabled={ isSaving || ! isDirty }
			/>
			<Button
				type="submit"
				form={ form }
				text={ __( 'Save', 'better-wp-security' ) }
				variant="primary"
				isBusy={ isSaving }
				disabled={ isSaving || ! isDirty }
			/>
		</StyledSettingsActions>
	);
}
