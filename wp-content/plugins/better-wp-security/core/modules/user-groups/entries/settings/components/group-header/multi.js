/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { GroupHeader } from './';

function MultiGroupHeader( { label } ) {
	return (
		<GroupHeader label={ label } />
	);
}

export default compose( [
	withSelect( ( select, { groupIds } ) => ( {
		label: groupIds.map( select( 'ithemes-security/user-groups' ).getMatchableLabel ).join( ', ' ),
	} ) ),
] )( MultiGroupHeader );
