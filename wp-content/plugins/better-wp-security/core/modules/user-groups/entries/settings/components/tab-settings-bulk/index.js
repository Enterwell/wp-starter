/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { TabBody, SettingsForm } from '../';
import Field from './field';

function TabSettingsBulk( { schema, hasEdits, save, isSaving, groupIds } ) {
	if ( ! schema ) {
		return null;
	}

	return (
		<TabBody name="settings">
			<TabBody.Row>
				<SettingsForm schema={ schema } settingComponent={ Field } groupIds={ groupIds } />
			</TabBody.Row>
			<TabBody.Row name="save">
				<Button disabled={ ! hasEdits } isPrimary onClick={ save } isBusy={ isSaving }>
					{ __( 'Save', 'better-wp-security' ) }
				</Button>
			</TabBody.Row>
		</TabBody>
	);
}

export default compose( [
	withSelect( ( select, { groupIds } ) => {
		return ( {
			schema: select( 'ithemes-security/core' ).getSchema( 'ithemes-security-user-group-settings' ),
			hasEdits: select( 'ithemes-security/user-groups-editor' ).hasBulkSettingEdits(),
			isSaving: select( 'ithemes-security/user-groups-editor' ).isSavingBulkEdits( groupIds ),
		} );
	} ),
	withDispatch( ( dispatch, { groupIds } ) => ( {
		save() {
			return dispatch( 'ithemes-security/user-groups-editor' ).saveBulkEdits( groupIds );
		},
	} ) ),
] )( TabSettingsBulk );
