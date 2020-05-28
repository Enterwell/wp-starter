/**
 * External dependencies
 */
import memize from 'memize';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { TabPanel } from '@ithemes/security-components';
import { MultiGroupHeader, TabSettingsBulk } from '../';

const getTabs = memize( () => ( [
	{
		name: 'settings',
		title: __( 'Features', 'better-wp-security' ),
		className: 'itsec-manage-user-group-tabs__tab',
		Component: TabSettingsBulk,
	},
] ) );

function ManageMultipleGroups( { groupIds } ) {
	return (
		<div className="itsec-manage-multiple-user-groups">
			<MultiGroupHeader groupIds={ groupIds } />
			<TabPanel tabs={ getTabs() } className="itsec-manage-user-group-tabs">
				{ ( { Component } ) => (
					<Component groupIds={ groupIds } />
				) }
			</TabPanel>
		</div>
	);
}

export default ManageMultipleGroups;
