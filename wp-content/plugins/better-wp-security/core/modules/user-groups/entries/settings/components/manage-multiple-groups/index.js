/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { store as uiStore } from '@ithemes/security.user-groups.ui';
import { TabSettingsBulk } from '../';
import { StyledTabPanel, StyledErrorList } from '../styles';

export default function ManageMultipleGroups( { groupIds } ) {
	const { errors } = useSelect( ( select ) => ( {
		errors: select(	uiStore ).getBulkErrorsList(),
	} ), [] );

	const tabs = useMemo( () => [
		{
			name: 'settings',
			title: __( 'Features', 'better-wp-security' ),
			Component: TabSettingsBulk,
		},
	], [] );

	return (
		<StyledTabPanel tabs={ tabs } isStyled>
			{ ( { Component } ) => (
				<Component groupIds={ groupIds }>
					<StyledErrorList errors={ errors } />
				</Component>
			) }
		</StyledTabPanel>
	);
}
