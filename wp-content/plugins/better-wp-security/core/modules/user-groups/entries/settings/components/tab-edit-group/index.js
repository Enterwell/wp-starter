/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';
import { CardBody, Disabled } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { EditGroupFields } from '../';

function TabEditGroup( { groupId, isLoading, children } ) {
	const body = (
		<>
			<CardBody className="itsec-user-groups-group-tab__edit-fields">
				{ children }
				<EditGroupFields groupId={ groupId } disabled={ isLoading } />
			</CardBody>
		</>
	);

	if ( isLoading ) {
		return <Disabled>{ body }</Disabled>;
	}

	return body;
}

export default compose( [
	withSelect( ( select, { groupId } ) => ( {
		isLoading:
			select( 'core/data' ).isResolving(
				'ithemes-security/user-groups',
				'getGroup',
				[ groupId ]
			) ||
			select( 'core/data' ).isResolving(
				'ithemes-security/core',
				'getIndex'
			),
	} ) ),
] )( TabEditGroup );
