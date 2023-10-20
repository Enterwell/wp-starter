/**
 * External dependencies
 */
import {
	Redirect,
	useLocation, useParams,
} from 'react-router-dom';
import { isPlainObject } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { Disabled } from '@wordpress/components';
import { useMemo } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { store as userGroupsStore } from '@ithemes/security.user-groups.api';
import { store as uiStore } from '@ithemes/security.user-groups.ui';
import { TabEditGroup, TabSettings } from '../';
import { StyledTabPanel, StyledErrorList } from '../styles';

export default function ManageGroup( { groupId } ) {
	const { root } = useParams();
	const { hash } = useLocation();
	const { type, isSaving, notFound, error } = useSelect(
		( select ) => ( {
			type: select( uiStore ).getMatchableType( groupId ),
			error: select( uiStore ).getError( groupId ),
			isSaving: select( uiStore ).isSavingGroupOrSettings( groupId ),
			notFound: select( userGroupsStore ).isGroupNotFound(
				groupId
			),
		} ),
		[ groupId ]
	);
	const tabs = useMemo(
		() =>
			[
				{
					name: 'settings',
					title: __( 'Features', 'better-wp-security' ),
					Component: TabSettings,
				},
				type === 'user-group' && {
					name: 'edit',
					title: __( 'Edit Group', 'better-wp-security' ),
					Component: TabEditGroup,
				},
			].filter( isPlainObject ),
		[ type ]
	);

	if ( notFound ) {
		return (
			<Redirect to={ `/${ root }/user-groups` } />
		);
	}

	return (
		<Disabled isDisabled={ isSaving }>
			<StyledTabPanel tabs={ tabs }>
				{ ( { Component } ) => (
					<Component groupId={ groupId } highlight={ hash.substring( 1 ) }>
						<StyledErrorList apiError={ error } />
					</Component>
				) }
			</StyledTabPanel>
		</Disabled>
	);
}
