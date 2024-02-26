/**
 * External dependencies
 */
import { Link, useHistory } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { useDispatch } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Solid WP dependencies
 */
import { Button, Surface, Text, TextSize, TextVariant, TextWeight } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { siteScannerStore, vulnerabilitiesStore } from '@ithemes/security.packages.data';
import { withNavigate } from '@ithemes/security-hocs';
import VulnerableSoftwareHeader from '../../components/vulnerable-software-header';
import { BeforeHeaderSlot } from '../../components/before-header';
import { StyledPageContainer, StyledPageHeader } from '../../components/styles';
import { StyledButtonsContainer, ScanningContainer, StyledSpinner } from './styles';

export default function Scan() {
	const history = useHistory();
	const { runScan, refreshQuery: refreshScans } = useDispatch( siteScannerStore );
	const { refreshQuery: refreshVulnerabilities } = useDispatch( vulnerabilitiesStore );
	useEffect( () => {
		runScan()
			.then( () => Promise.allSettled( [
				refreshScans( 'main' ),
				refreshVulnerabilities( 'main' ),
			] ) )
			.then( () => history.replace( '/active' ) );
	}, [ history, refreshScans, refreshVulnerabilities, runScan ] );

	return (
		<StyledPageContainer>
			<BeforeHeaderSlot />
			<StyledPageHeader>
				<StyledButtonsContainer>
					<Link to="/database" component={ withNavigate( Button ) } text={ __( 'Browse Vulnerability Database', 'better-wp-security' ) } />
					<Link to="/scan" component={ withNavigate( Button ) } variant="primary" disabled text={ __( 'Scan for vulnerabilities', 'better-wp-security' ) } />
				</StyledButtonsContainer>
			</StyledPageHeader>
			<Surface as="section">
				<VulnerableSoftwareHeader />
				<table className="itsec-card-vulnerable-software__table">
					<thead>
						<tr>
							<Text as="th" text={ __( 'Type', 'better-wp-security' ) } />
							<Text as="th" text={ __( 'Vulnerability', 'better-wp-security' ) } />
							<Text as="th" text={ __( 'Severity', 'better-wp-security' ) } />
							<Text as="th" text={ __( 'Status', 'better-wp-security' ) } />
							<Text as="th" text={ __( 'Date', 'better-wp-security' ) } />
							<Text as="th" text={ __( 'Action', 'better-wp-security' ) } />
						</tr>
					</thead>
					<tbody>
						<tr>
							<td colSpan={ 6 }>
								<ScanningContainer>
									<StyledSpinner />
									<Text
										text={ __( 'Scanning for vulnerabilities…', 'better-wp-security' ) }
										size={ TextSize.LARGE }
										weight={ TextWeight.HEAVY }
										variant={ TextVariant.DARK }
									/>
									<Text
										text={ __( 'Currently checking your site for any vulnerable plugins, themes, or WordPress Core.', 'better-wp-security' ) }
										size={ TextSize.SMALL }
										align="center"
										variant={ TextVariant.MUTED }
									/>
								</ScanningContainer>
							</td>
						</tr>
					</tbody>
				</table>
			</Surface>
		</StyledPageContainer>
	);
}
