/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

/**
 * Solid dependencies
 */
import { PageHeader } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { Save, SettingsForm } from '..';
import { StyledSettings } from './styles';

export default function Settings( {
	usersAndRoles,
	apiError,
} ) {
	const [ errors, setErrors ] = useState( [] );

	return (
		<>
			<StyledSettings>
				<PageHeader
					title={ __( 'Notifications', 'better-wp-security' ) }
					description={ __(
						'Manage and configure email notifications sent by Solid Security related to various features.',
						'better-wp-security'
					) }
					hasBorder
				/>
				<SettingsForm usersAndRoles={ usersAndRoles } errors={ errors } apiError={ apiError } hasPadding />
			</StyledSettings>
			<Save setErrors={ setErrors } />
		</>
	);
}
