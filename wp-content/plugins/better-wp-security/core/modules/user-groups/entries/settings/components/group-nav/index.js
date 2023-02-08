/**
 * External dependencies
 */
import memize from 'memize';
import { filter } from 'lodash';

/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';
import { Icon } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Fragment, useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { ControlledMultiTabPanel } from '@ithemes/security-components';
import { ManageGroup, ManageMultipleGroups } from '../';
import './style.scss';

const convertToTabs = memize( ( groups ) => groups
	.sort( ( groupA, groupB ) => {
		if ( groupA.type === groupB.type ) {
			return 0;
		}

		if ( groupA.type === 'user-group' ) {
			return -1;
		}

		if ( groupB.type === 'user-group' ) {
			return 1;
		}

		return 0;
	} )
	.map( ( group ) => ( {
		name: group.id,
		title: group.label,
		className: 'itsec-user-groups-list__item',
		group,
	} ) )
	.concat( {
		name: 'new',
		title: (
			<Fragment>
				<Icon icon="plus" />
				{ __( 'New Group', 'better-wp-security' ) }
			</Fragment>
		),
		className: 'itsec-user-groups-list__item itsec-user-groups-list__item--new',
		allowMultiple: false,
	} )
);

/**
 * Group Navigation component.
 *
 * @return {Element|null}
 */
function GroupNav( { matchables, resolvingMatchables, selectedGroup, selectGroup } ) {
	useEffect( () => {
		if ( ! resolvingMatchables && matchables.length && selectedGroup.length === 0 ) {
			selectGroup( [ matchables[ 0 ].id ] );
		}
	}, [ resolvingMatchables ] );

	if ( resolvingMatchables && ! matchables.length ) {
		return null;
	}

	const tabs = convertToTabs( matchables );

	return (
		<ControlledMultiTabPanel
			tabs={ tabs }
			selected={ selectedGroup }
			onSelect={ selectGroup }
			allowMultiple
			orientation="vertical"
			className="itsec-user-groups-list"
		>
			{ ( selectedTabs ) => {
				if ( selectedTabs.length > 1 ) {
					const groupIds = filter( selectedTabs.map( ( { group } ) => group && group.id ) );

					return <ManageMultipleGroups groupIds={ groupIds } />;
				}

				if ( selectedTabs[ 0 ] ) {
					return <ManageGroup groupId={ selectedTabs[ 0 ].name } isNew={ selectedTabs[ 0 ].name === 'new' } />;
				}

				return null;
			} }
		</ControlledMultiTabPanel>
	);
}

export default compose( [
	withSelect( ( select ) => ( {
		matchables: select( 'ithemes-security/user-groups' ).getMatchables(),
		resolvingMatchables: select( 'core/data' ).isResolving( 'ithemes-security/user-groups', 'getMatchables' ),
		selectedGroup: select( 'ithemes-security/user-groups-editor' ).getSelectedGroup(),
	} ) ),
	withDispatch( ( dispatch ) => ( {
		selectGroup: dispatch( 'ithemes-security/user-groups-editor' ).selectGroup,
	} ) ),
] )( GroupNav );
