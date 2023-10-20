/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { createInterpolateElement, useState } from '@wordpress/element';
import { Modal } from '@wordpress/components';

/**
 * Internal dependencies
 */
import {
	CardHeader,
	CardHeaderTitle,
	CardFooterSchemaActions,
} from '@ithemes/security.dashboard.dashboard';
import { SiteScanResults } from '@ithemes/security-components';

export default function ScanOnly( { card, config } ) {
	const [ scanResults, setScanResults ] = useState( undefined );
	return (
		<div className="itsec-card--type-malware-scan itsec-card--type-malware--scan-only">
			<CardHeader>
				<CardHeaderTitle card={ card } config={ config } />
			</CardHeader>
			<section className="itsec-card-malware-scan__description">
				<p>
					{ createInterpolateElement(
						__(
							'This <a>site scan is powered by SolidWP</a>. We check for Google Safe Browsing blocklist status, website errors, and out-of-date software. These data points are not 100% accurate, but we try our best to provide thorough results.',
							'better-wp-security'
						),
						{
							a: (
								// eslint-disable-next-line jsx-a11y/anchor-has-content
								<a href="https://go.solidwp.com/solid-security-site-scanner-help-center" />
							),
						}
					) }
				</p>
				<p>
					{ __(
						'Enable Database Logging to see a history of completed Site Scans.',
						'better-wp-security'
					) }
				</p>
			</section>
			<CardFooterSchemaActions
				card={ card }
				onComplete={ ( href, response ) =>
					href.endsWith( '/scan' ) &&
					setScanResults( response )
				}
			/>
			{ scanResults && (
				<Modal
					title={ __( 'Scan Results', 'better-wp-security' ) }
					onRequestClose={ () =>
						setScanResults( undefined )
					}
				>
					<SiteScanResults
						results={ scanResults }
						showSiteUrl={ false }
					/>
				</Modal>
			) }
		</div>
	);
}
