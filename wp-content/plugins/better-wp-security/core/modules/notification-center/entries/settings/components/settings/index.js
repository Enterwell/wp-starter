/**
 * WordPress dependencies
 */
import { TextControl, Button } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { HelpList } from '@ithemes/security-components';
import {
	HelpFill,
	PageHeader,
	PrimaryForm,
	PrimaryFormSection,
	useModuleSchemaValidator,
} from '@ithemes/security.pages.settings';
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import { UserRoleList } from '..';

export default function Settings( {
	usersAndRoles,
	onSubmit,
	saveLabel = __( 'Save All', 'better-wp-security' ),
	allowUndo = true,
	allowCleanSave = false,
	apiError,
} ) {
	const validator = useModuleSchemaValidator( 'notification-center' );
	const [ errors, setErrors ] = useState( [] );
	const { isDirty, isSaving, fromEmail, defaultRecipients } = useSelect(
		( select ) => ( {
			isDirty: select( MODULES_STORE_NAME ).areSettingsDirty(
				'notification-center'
			),
			isSaving: select( MODULES_STORE_NAME ).isSavingSettings(
				'notification-center'
			),
			fromEmail:
				select( MODULES_STORE_NAME ).getEditedSetting(
					'notification-center',
					'from_email'
				) || '',
			defaultRecipients:
				select( MODULES_STORE_NAME ).getEditedSetting(
					'notification-center',
					'default_recipients'
				) || {},
		} ),
		[]
	);
	const { editSetting, resetSettingEdits } = useDispatch(
		MODULES_STORE_NAME
	);

	const maybeSubmit = () => {
		const isValid = validator();

		if ( isValid === true ) {
			setErrors( [] );
			onSubmit();
		} else {
			setErrors( isValid.errorText );
		}
	};

	return (
		<>
			<PageHeader
				title={ __( 'Notification Center', 'better-wp-security' ) }
				description={ __(
					'Manage and configure email notifications sent by iThemes Security related to various settings modules.',
					'better-wp-security'
				) }
			/>
			<PrimaryForm
				saveLabel={ saveLabel }
				saveDisabled={ ! isDirty && ! allowCleanSave }
				isSaving={ isSaving }
				onSubmit={ maybeSubmit }
				apiError={ apiError }
				errors={ errors }
				buttons={
					allowUndo && [
						<Button
							key="undo"
							onClick={ () =>
								resetSettingEdits( 'notification-center' )
							}
							disabled={ ! isDirty }
						>
							{ __( 'Undo Changes', 'better-wp-security' ) }
						</Button>,
					]
				}
			>
				<TextControl
					type="email"
					value={ fromEmail }
					onChange={ ( email ) =>
						editSetting(
							'notification-center',
							'from_email',
							email
						)
					}
					label={ __( 'From Email', 'better-wp-security' ) }
					help={ __(
						'iThemes Security will send notifications from this email address. Leave blank to use the WordPress default.',
						'better-wp-security'
					) }
				/>
				<PrimaryFormSection heading={ __( 'Default Recipients' ) }>
					<UserRoleList
						help={ __(
							'Set the default recipients for any admin-facing notifications.',
							'better-wp-security'
						) }
						value={ defaultRecipients.user_list || [] }
						onChange={ ( recipients ) =>
							editSetting(
								'notification-center',
								'default_recipients',
								{ ...defaultRecipients, user_list: recipients }
							)
						}
						usersAndRoles={ usersAndRoles }
					/>
				</PrimaryFormSection>
			</PrimaryForm>
			<HelpFill>
				<PageHeader title={ __( 'Notifications', 'better-wp-security' ) } />
				<HelpList topic="notification-center" />
			</HelpFill>
		</>
	);
}
