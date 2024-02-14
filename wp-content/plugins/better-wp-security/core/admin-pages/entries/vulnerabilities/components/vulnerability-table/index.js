/**
 * External dependencies
 */
import { Link } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useViewportMatch } from '@wordpress/compose';
import {
	brush as themeIcon,
	check as checkIcon,
	closeSmall as closeIcon,
	plugins as pluginIcon,
	wordpress as coreIcon,
} from '@wordpress/icons';

/**
 * SolidWP dependencies
 */
import { Text } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { VulnerabilityMitigated, VulnerabilityMuted } from '@ithemes/security-style-guide';
import { NoVulnerabilitiesEmptyState } from '../no-vulnerabilities-empty-state';
import {
	StyledTableSection,
	StyledRow,
	StyledVulnerabilityName,
	StyledVulnerabilityVersion,
	StyledVulnerabilityDetail,
	StyledCombinedColumns,
	StyledVulnerability,
	StyledSeverity,
	StyledStatusCheck,
	StyledStatusRedCircle,
} from './styles';

export function vulnerabilityIcon( type ) {
	switch ( type ) {
		case 'plugin':
			return pluginIcon;
		case 'theme':
			return themeIcon;
		case 'wordpress':
			return coreIcon;
		default:
			return undefined;
	}
}

export function severityColor( score ) {
	switch ( true ) {
		case isNaN( score ):
			return '#CECECE';
		case score < 3:
			return '#B8E6BF';
		case score < 7:
			return '#FFC518';
		case score < 9:
			return '#FFABAF';
		default:
			return '#D63638';
	}
}

function statusIcon( status ) {
	switch ( status ) {
		case '':
			return <StyledStatusRedCircle icon={ closeIcon } style={ { fill: '#D75A4B' } } />;
		case 'auto-updated':
		case 'deactivated':
		case 'deleted':
		case 'updated':
			return <StyledStatusCheck icon={ checkIcon } style={ { fill: '#FFFFFF' } } />;
		case 'patched':
			return <VulnerabilityMitigated />;
		case 'muted':
			return <VulnerabilityMuted />;
		default:
	}
}
export default function VulnerabilityTable( { getScans, items, filters } ) {
	const isSmall = useViewportMatch( 'small', '<' );
	const isLarge = useViewportMatch( 'large' );

	return (
		<StyledTableSection as="section">
			<table className="itsec-card-vulnerable-software__table">
				<thead>
					{ isSmall ? (
						<tr>
							<Text as="th" text={ __( 'Type', 'better-wp-security' ) } />
							<Text as="th" text={ __( 'Severity and Name', 'better-wp-security' ) } />
							<Text as="th" text={ __( 'Status', 'better-wp-security' ) } />
							<Text as="th" text={ __( 'Action', 'better-wp-security' ) } />
						</tr>
					) : (
						<tr>
							<Text as="th" text={ __( 'Type', 'better-wp-security' ) } />
							<Text as="th" text={ __( 'Vulnerability', 'better-wp-security' ) } />
							<Text as="th" text={ __( 'Severity', 'better-wp-security' ) } />
							<Text as="th" text={ __( 'Status', 'better-wp-security' ) } />
							<Text as="th" text={ __( 'Date', 'better-wp-security' ) } />
							<Text as="th" text={ __( 'Action', 'better-wp-security' ) } />
						</tr>
					) }

				</thead>
				{ items.length > 0 &&
					( <tbody>
						{ items.map( ( vulnerability ) => {
							const id = vulnerability.details.id;
							return (
								<StyledRow key={ id } isSmall={ isSmall } isLarge={ isLarge }>
									{ isSmall ? (
										<>
											<td><Text icon={ vulnerabilityIcon( vulnerability.software.type.slug ) } /></td>
											<td>
												<StyledCombinedColumns>
													<StyledSeverity backgroundColor={ severityColor( vulnerability.details.score ) } isSmall={ isSmall } status={ vulnerability.status } weight={ 600 } text={ vulnerability.details.score ?? '??' } />
													<StyledVulnerabilityName weight={ 500 } text={ vulnerability.software.label || vulnerability.software.slug } />
												</StyledCombinedColumns>
											</td>
											<td>
												<Text icon={ statusIcon( vulnerability.resolution.slug ) } iconSize={ 16 } text={ vulnerability.resolution.label } />
											</td>
											<td>
												<Link to={ `/vulnerability/${ id }` }>{ __( 'View Details', 'better-wp-security' ) }</Link>
											</td>
										</>
									) : (
										<>
											<td><Text icon={ vulnerabilityIcon( vulnerability.software.type.slug ) } text={ vulnerability.software.type.label } /></td>
											<td>
												<StyledVulnerability isLarge={ isLarge }>
													<StyledVulnerabilityName weight={ 500 } text={ vulnerability.software.label || vulnerability.software.slug } />
													<StyledVulnerabilityVersion text={ vulnerability.details.affected_in } />
													<StyledVulnerabilityDetail text={ vulnerability.details.type.label } />
												</StyledVulnerability>
											</td>
											<td><StyledSeverity backgroundColor={ severityColor( vulnerability.details.score ) } status={ vulnerability.status } weight={ 600 } text={ vulnerability.details.score ?? '??' } /></td>
											<td>
												<Text icon={ statusIcon( vulnerability.resolution.slug ) } iconSize={ 16 } text={ vulnerability.resolution.label } />
											</td>
											<td>
												<Text
													text={ sprintf(
													/* translators: 1. Human time diff. */
														__( '%s ago', 'better-wp-security' ),
														vulnerability.last_seen_diff
													) }
												/>
											</td>
											<td>
												<Link to={ `/vulnerability/${ id }` }>{ __( 'View Details', 'better-wp-security' ) }</Link>
											</td>
										</>
									) }

								</StyledRow>
							);
						} ) }
					</tbody> )
				}

				{ ( items.length === 0 && filters?.resolution?.includes( '' ) ) &&
					(
						<tbody>
							<tr>
								<td colSpan="6">
									<NoVulnerabilitiesEmptyState getScans={ getScans } />
								</td>
							</tr>
						</tbody>
					)
				}

				{ ( items.length === 0 && ! filters?.resolution?.includes( '' ) ) &&
					( <tbody><tr><td colSpan="6">{ __( 'No vulnerabilities found', 'better-wp-security' ) }</td></tr></tbody> )
				}

			</table>
		</StyledTableSection>
	);
}
