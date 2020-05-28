/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { isWPError } from '@ithemes/security-utils';
import WPErrorDetails from './wp-error-details';
import SystemErrorDetails from './system-error-details';
import MalwareDetails from './malware-details';
import BlacklistDetails from './blacklist-details';
import KnownVulnerabilities from './known-vulnerabilities';
import './style.scss';

function SiteScanResults( { results, showSiteUrl = true, showErrorDetails = true } ) {
	const siteUrl = results.url;

	return (
		<div className="itsec-site-scan-results">
			{ showSiteUrl && siteUrl && <h4>{ sprintf( __( 'Site: %s', 'better-wp-security' ), siteUrl ) }</h4> }

			{ isWPError( results ) ? <WPErrorDetails results={ results } showErrorDetails={ showErrorDetails } /> : (
				<Fragment>
					<SystemErrorDetails results={ results } />
					<KnownVulnerabilities results={ results } />
					<MalwareDetails results={ results } />
					<BlacklistDetails results={ results } />
				</Fragment>
			) }
		</div>
	);
}

export default SiteScanResults;
