/**
 * External dependencies
 */
import classnames from 'classnames';
import memize from 'memize';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { dateI18n } from '@wordpress/date';
import { Button, Modal } from '@wordpress/components';
import { useInstanceId } from '@wordpress/compose';
import { useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { SiteScanResults, PrintR } from '@ithemes/security-components';
import {
	CardHeader,
	CardHeaderTitle,
	CardHeaderDate,
	CardFooterSchemaActions,
} from '@ithemes/security.dashboard.dashboard';
import ScanOnly from './scan-only';
import './style.scss';

const isSameUrl = memize(
	( a, b ) =>
		String( a ).replace( /https?:\/\//, '' ) ===
		String( b ).replace( /https?:\/\//, '' )
);

function MalwareScan( { card, config } ) {
	const instanceId = useInstanceId( MalwareScan );
	const { siteInfo } = useSelect( ( select ) => ( {
		siteInfo: select( 'ithemes-security/core' ).getSiteInfo(),
	} ) );

	const [ viewEntry, setViewEntry ] = useState( 0 );
	const [ scanResults, setScanResults ] = useState( undefined );
	const [ showRawDetails, setShowRawDetails ] = useState( false );

	return (
		<div className="itsec-card--type-malware-scan">
			<CardHeader>
				<CardHeaderTitle card={ card } config={ config } />
				<CardHeaderDate card={ card } config={ config } />
			</CardHeader>
			<section className="itsec-card-malware-scan__scans-section">
				<table className="itsec-card-malware-scan__scans">
					<thead>
						<tr>
							<th>{ __( 'Time', 'better-wp-security' ) }</th>
							<th>{ __( 'Status', 'better-wp-security' ) }</th>
							<th>
								<span className="screen-reader-text">
									{ __( 'Actions', 'better-wp-security' ) }
								</span>
							</th>
						</tr>
					</thead>
					<tbody>
						{ card.data.scans.map( ( scan ) => {
							const id = scan.id;
							const status = scan.status;
							const label = scan.description;

							return (
								<tr key={ id }>
									<th scope="row">
										{ dateI18n(
											'M d, Y g:i A',
											scan.time
										) }
									</th>
									<td>
										<span
											className={ classnames(
												'itsec-card-malware-scan__scan-status',
												`itsec-card-malware-scan__scan-status--${ status }`
											) }
										>
											{ label }
										</span>
									</td>
									<td>
										<Button
											variant="link"
											aria-pressed={ viewEntry === id }
											onClick={ () => setViewEntry( id ) }
										>
											{ __( 'View', 'better-wp-security' ) }
										</Button>
										{ viewEntry === id && (
											<Modal
												title={ sprintf(
													/* translators: 1. Formatted date. */
													__(
														'View Scan Details for %s',
														'better-wp-security'
													),
													dateI18n(
														'M d, Y g:i A',
														scan.time
													)
												) }
												onRequestClose={ () => {
													setViewEntry( 0 );
													setShowRawDetails( false );
												} }
											>
												<SiteScanResults
													results={ scan }
													showSiteUrl={
														! isSameUrl(
															scan.url,
															siteInfo?.url
														)
													}
												/>
												<Button
													className="itsec-card-malware-scan__raw-details-toggle"
													variant="link"
													onClick={ () =>
														setShowRawDetails(
															! showRawDetails
														)
													}
													aria-expanded={
														showRawDetails
													}
													aria-controls={ `itsec-card-malware-scan__raw-details--${ instanceId }` }
												>
													{ showRawDetails
														? __(
															'Hide Raw Details',
															'better-wp-security'
														)
														: __(
															'Show Raw Details',
															'better-wp-security'
														) }
												</Button>
												<div
													id={ `itsec-card-malware-scan__raw-details--${ instanceId }` }
													style={ {
														visibility: showRawDetails
															? 'visible'
															: 'hidden',
													} }
												>
													{ showRawDetails && (
														<PrintR json={ scan } />
													) }
												</div>
											</Modal>
										) }
									</td>
								</tr>
							);
						} ) }
					</tbody>
				</table>
			</section>
			<CardFooterSchemaActions
				card={ card }
				onComplete={ ( href, response ) =>
					href.endsWith( '/scan' ) && setScanResults( response )
				}
			/>
			{ scanResults && (
				<Modal
					title={ __( 'Scan Results', 'better-wp-security' ) }
					onRequestClose={ () => setScanResults( undefined ) }
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

export const slug = 'malware-scan';
export const settings = {
	render( props ) {
		if ( props.card?.data.log_type === 'file' ) {
			return <ScanOnly { ...props } />;
		}

		return <MalwareScan { ...props } />;
	},
};
