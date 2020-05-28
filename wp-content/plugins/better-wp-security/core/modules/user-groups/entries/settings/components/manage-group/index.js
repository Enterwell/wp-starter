/**
 * External dependencies
 */
import memize from 'memize';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { TabPanel } from '@ithemes/security-components';
import { TabCreateGroup, TabEditGroup, TabSettings, NewGroupHeader, SingleGroupHeader } from '../';
import './style.scss';

const getTabs = memize( ( groupId, type ) => {
	if ( groupId === 'new' ) {
		return [
			{
				name: 'create',
				title: __( 'Edit Group', 'better-wp-security' ),
				className: 'itsec-manage-user-group-tabs__tab',
				Component: TabCreateGroup,
			},
		];
	}

	const tabs = [
		{
			name: 'settings',
			title: __( 'Features', 'better-wp-security' ),
			className: 'itsec-manage-user-group-tabs__tab',
			Component: TabSettings,
		},
	];

	if ( type === 'user-group' ) {
		tabs.push( {
			name: 'edit',
			title: __( 'Edit Group', 'better-wp-security' ),
			className: 'itsec-manage-user-group-tabs__tab',
			Component: TabEditGroup,
		} );
	}

	return tabs;
} );

function ManageGroup( { groupId, type, isNew } ) {
	return (
		<div className="itsec-manage-user-group">
			{ isNew ? <NewGroupHeader /> : <SingleGroupHeader groupId={ groupId } /> }
			<TabPanel tabs={ getTabs( groupId, type ) } className="itsec-manage-user-group-tabs">
				{ ( { Component } ) => (
					<Component groupId={ groupId } />
				) }
			</TabPanel>
		</div>
	);
}

export default compose( [
	withSelect( ( select, { groupId } ) => ( {
		type: select( 'ithemes-security/user-groups' ).getMatchableType( groupId ),
	} ) ),
] )( ManageGroup );
