/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { castWPError, isWPError } from '@ithemes/security-utils';
import WPErrorDetails from './wp-error-details';
import SystemErrorDetails from './system-error-details';
import Entry from './entry';
import './style.scss';

function SiteScanResults( { results, showSiteUrl = true, showErrorDetails = true } ) {
	const siteUrl = results.url;
	let error;

	if ( isWPError( results ) ) {
		error = castWPError( results );
	} else if ( results.code === 'error' ) {
		error = castWPError( results.errors[ 0 ] );
	}

	return (
		<div className="itsec-site-scan-results">
			{ showSiteUrl && siteUrl && <h4>{ sprintf( __( 'Site: %s', 'better-wp-security' ), siteUrl ) }</h4> }

			{ error ? <WPErrorDetails results={ error } showErrorDetails={ showErrorDetails } /> : (
				<Fragment>
					<SystemErrorDetails results={ results } />
					{ results.entries.map( ( entry, i ) => (
						<Entry results={ results } entry={ entry } key={ i } />
					) ) }
				</Fragment>
			) }
		</div>
	);
}

export default SiteScanResults;
