/**
 * External dependencies
 */
import memize from 'memize';
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { dateI18n } from '@wordpress/date';
import { Modal } from '@wordpress/components';
import { useInstanceId } from '@wordpress/compose';
import { useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * SolidWP dependencies
 */
import { Button, Surface, Text, TextWeight } from '@ithemes/ui';
import { DateRangeControl, HiResIcon } from '@ithemes/security-ui';
import { SiteScanResults, PrintR } from '@ithemes/security-components';
import { NoScans } from '@ithemes/security-style-guide';

/**
 * Internal dependencies
 */
import {
	CardHeader,
	CardHeaderTitle,
	CardFooterSchemaActions,
} from '@ithemes/security.dashboard.dashboard';
import ScanOnly from './scan-only';

const isSameUrl = memize(
	( a, b ) =>
		String( a ).replace( /https?:\/\//, '' ) ===
		String( b ).replace( /https?:\/\//, '' )
);

const StyledNoScans = styled( Surface )`
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	gap: ${ ( { theme: { getSize } } ) => getSize( 0.5 ) };
	padding: ${ ( { theme: { getSize } } ) => `${ getSize( 1.5 ) } ${ getSize( 0.5 ) }` };
`;

function EmptyState() {
	return (
		<StyledNoScans>
			<HiResIcon icon={ <NoScans /> } isSmall />
			<Text weight={ TextWeight.HEAVY } text={ __( 'No site scans have been made recently', 'better-wp-security' ) } />
			<Text align="center" text={ __( 'Scan your site to see results and details shown here!', 'better-wp-security' ) } />
		</StyledNoScans>
	);
}

const StyledActions = styled( Text )`
	text-align: right !important;
`;

function MalwareScan( { card, config } ) {
	const instanceId = useInstanceId( MalwareScan );
	const { period, siteInfo } = useSelect( ( select ) => ( {
		period: select( 'ithemes-security/dashboard' ).getDashboardCardQueryArgs( card.id )?.period ??
			config.query_args.period?.default,
		siteInfo: select( 'ithemes-security/core' ).getSiteInfo(),
	} ), [ card.id, config ] );

	const [ viewEntry, setViewEntry ] = useState( 0 );
	const [ scanResults, setScanResults ] = useState( undefined );
	const [ showRawDetails, setShowRawDetails ] = useState( false );

	const { queryDashboardCard } = useDispatch( 'ithemes-security/dashboard' );

	const onPeriodChange = ( newPeriod ) => {
		return queryDashboardCard( card.id, { period: newPeriod } );
	};

	return (
		<div className="itsec-card--type-malware-scan">
			<CardHeader>
				<CardHeaderTitle card={ card } config={ config } />
				<DateRangeControl value={ period } onChange={ onPeriodChange } />
			</CardHeader>
			<section className="itsec-card-malware-scan__scans-section">
				<table className="itsec-card-malware-scan__scans">
					<thead>
						<tr>
							<Text
								as="th"
								textTransform="capitalize"
								text={ __( 'Date', 'better-wp-security' ) }
							/>
							<Text
								as="th"
								textTransform="capitalize"
								text={ __( 'Scan Status', 'better-wp-security' ) }
							/>
							<StyledActions
								as="th"
								textTransform="capitalize"
								text={ __( 'Actions', 'better-wp-security' ) }
							/>
						</tr>
					</thead>
					<tbody>
						{ card.data.scans.length === 0
							? <tr>
								<td colSpan="6"><EmptyState /></td>
							</tr>
							: card.data.scans.map( ( scan ) => {
								const id = scan.id;
								const status = scan.status;
								const label = scan.description;

								return (
									<tr key={ id }>
										<Text
											as="th"
											text={ dateI18n(
												'M d, Y g:i A',
												scan.time
											) }
										/>
										<td>
											<Text
												indicator={ ( ) => {
													if ( status === 'clean' ) {
														return '#7ad03a';
													} else if ( status === 'error' ) {
														return '#dd3d36';
													}
													return '#ffb900';
												} }
												text={ label }
											/>
										</td>
										<StyledActions as="td">
											<Button
												variant="link"
												aria-pressed={ viewEntry === id }
												onClick={ () => setViewEntry( id ) }
												align="right"
											>
												{ __( 'View Results', 'better-wp-security' ) }
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
													className="itsec-apply-css-vars"
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
										</StyledActions>
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
					className="itsec-apply-css-vars"
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
