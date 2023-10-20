/**
 * WordPress dependencies
 */
import { TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Solid dependencies
 */
import { Heading, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { PrimaryForm } from '@ithemes/security.pages.settings';
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import { UserRoleList } from '../';

export default function SettingsForm( { usersAndRoles, errors, apiError, hasPadding } ) {
	const { fromEmail, defaultRecipients } = useSelect(
		( select ) => ( {
			isDirty: select( MODULES_STORE_NAME ).areSettingsDirty(
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
	const { editSetting } = useDispatch(
		MODULES_STORE_NAME
	);

	return (
		<PrimaryForm apiError={ apiError } errors={ errors } hasPadding={ hasPadding }>
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
					'Solid Security will send notifications from this email address. Leave blank to use the WordPress default.',
					'better-wp-security'
				) }
			/>
			<Heading
				level={ 3 }
				size={ TextSize.LARGE }
				weight={ TextWeight.HEAVY }
				variant={ TextVariant.DARK }
				text={ __( 'Default Recipients', 'better-wp-security' ) }
			/>
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
		</PrimaryForm>
	);
}
