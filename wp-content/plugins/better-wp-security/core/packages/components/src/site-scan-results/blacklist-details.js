/**
 * External dependencies
 */
import { get } from 'lodash';
import memize from 'memize';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import WrappedSection from './wrapped-section';
import Detail from './detail';

const sortBlacklist = memize( ( blacklist ) => {
	return [ ...blacklist ].sort( ( a, b ) => {
		if ( a.status === 'blacklisted' && b.status !== 'blacklisted' ) {
			return -1;
		}

		if ( a.status !== 'blacklisted' && b.status === 'blacklisted' ) {
			return 1;
		}

		return 0;
	} );
} );

function BlacklistDetails( { results } ) {
	const blacklist = sortBlacklist( results.entries.blacklist );
	const status = get( blacklist, [ 0, 'status' ] ) === 'blacklisted' ? 'warn' : 'clean';

	return (
		<WrappedSection type="malware" status={ status } description={ __( 'Blacklist', 'better-wp-security' ) }>
			{ blacklist.map( ( entry, i ) => (
				<Detail key={ i } status={ entry.status === 'blacklisted' ? 'warn' : 'clean' }>
					<a href={ entry.report_details }>
						{ entry.status === 'blacklisted' ?
							sprintf( __( 'Domain blacklisted by %s', 'better-wp-security' ), entry.vendor.label ) :
							sprintf( __( 'Domain clean by %s', 'better-wp-security' ), entry.vendor.label )
						}
					</a>
				</Detail>
			) ) }
		</WrappedSection>
	);
}

export default BlacklistDetails;
