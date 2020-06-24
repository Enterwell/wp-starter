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
import './style.scss';

function TabSettings( { schema, groupId, hasEdits, save, isSaving, isLoading } ) {
	if ( ! schema ) {
		return null;
	}

	return (
		<TabBody name="settings" isLoading={ isLoading }>
			<TabBody.Row>
				<SettingsForm schema={ schema } settingComponent={ Field } groupId={ groupId } disabled={ isLoading } />
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
	withSelect( ( select, { groupId } ) => ( {
		groupSettings: select( 'ithemes-security/user-groups' ).getGroupSettings( groupId ), // Hack to make sure isResolving is triggered in this component
		isLoading: select( 'core/data' ).isResolving( 'ithemes-security/user-groups', 'getGroupSettings', [ groupId ] ),
		schema: select( 'ithemes-security/core' ).getSchema( 'ithemes-security-user-group-settings' ),
		hasEdits: select( 'ithemes-security/user-groups-editor' ).settingHasEdits( groupId ),
		isSaving: select( 'ithemes-security/user-groups' ).isUpdatingSettings( groupId ),
	} ) ),
	withDispatch( ( dispatch, { groupId } ) => ( {
		save() {
			return dispatch( 'ithemes-security/user-groups-editor' ).saveGroupSettings( groupId );
		},
	} ) ),
] )( TabSettings );
