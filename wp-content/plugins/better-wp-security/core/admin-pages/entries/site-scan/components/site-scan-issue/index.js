/**
 * WordPress dependencies
 */
import { useViewportMatch } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import TableRow from './table-row';
import ListItem from './list-item';

export default function SiteScanIssue( { issue, icon, children } ) {
	const isSmall = useViewportMatch( 'small', '<' );
	const isLarge = useViewportMatch( 'large' );

	if ( isSmall ) {
		return <ListItem issue={ issue } icon={ icon } children={ children } />;
	}
	return <TableRow issue={ issue } icon={ icon } children={ children } isLarge={ isLarge } />;
}
