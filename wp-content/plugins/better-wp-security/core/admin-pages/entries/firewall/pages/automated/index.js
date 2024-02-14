/**
 * External dependencies
 */
import moment from 'moment';

/**
 * WordPress dependencies
 */
import { __, sprintf, _n } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import {
	check as checkIcon,
	closeSmall as dismissIcon,
	external as externalIcon,
} from '@wordpress/icons';
import { useViewportMatch } from '@wordpress/compose';
import { Flex } from '@wordpress/components';
import { createInterpolateElement, useEffect } from '@wordpress/element';

/**
 * SolidWP dependencies
 */
import { Badge, TextSize, TextVariant, Text } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import {
	severityColor,
	vulnerabilityIcon,
} from '@ithemes/security.pages.vulnerabilities';
import {
	coreStore,
	vulnerabilitiesStore,
} from '@ithemes/security.packages.data';
import {
	PurpleShield,
	BasicToProShield,
	ProPlusPatchstack, VulnerabilitySuccess,
} from '@ithemes/security-style-guide';
import {
	HiResIcon,
	ActiveUpdatesBadge,
	VirtualPatchingBadge,
} from '@ithemes/security-ui';
import { useLocalStorage } from '@ithemes/security-hocs';
import { Page } from '../../components';
import {
	StyledAutomatedBannerSurface,
	StyledAutomatedCardHeader,
	StyledAutomatedCardSurface,
	StyledCardText,
	StyledPatchstackMark,
	StyledBrand,
	StyledLogoImage,
	StyledLogoText,
	StyledAutomatedVulnerabilityTableHeader,
	StyledTableSection,
	StyledCombinedColumns,
	StyledVulnerabilityName,
	StyledVulnerabilityVersion,
	StyledVulnerabilityDetail,
	StyledSeverity,
	StyledThead,
	StyledTableContainer,
	StyledTable,
	StyledTableCardContainer,
	StyledColumnContainer,
	StyledVulnerabilityIcon,
	StyledBannerTitle,
	StyledVulnerabilityTableHeaderText,
	StyledButton,
	StyledBadge,
	StyledGridContainer,
	StyledNoVulnerabilitiesContainer,
	StyledNoVulnerabilitiesButton,
	StyledHasPatchstackDismiss,
} from './styles';

const patchableQuery = {
	patchable: true,
	per_page: 100,
	last_seen_after: moment().subtract( 90, 'days' ).toISOString(),
};

const autoUpdatedQuery = {
	resolution: 'auto-updated',
	per_page: 100,
	last_seen_after: moment().subtract( 90, 'days' ).toISOString(),
	software_type: [ 'plugin', 'theme' ],
};

export default function Automated() {
	const isSmall = useViewportMatch( 'small', '<' );
	const isMedium = useViewportMatch( 'medium', '<' );
	const isLarge = useViewportMatch( 'large' );
	const { items, hasPatchstack, installType, isQuerying } = useSelect( ( select ) => ( {
		items: select( vulnerabilitiesStore ).getQueryResults( 'autoPatched' ),
		hasPatchstack: select( coreStore ).hasPatchstack(),
		installType: select( coreStore ).getInstallType(),
		isQuerying: select( vulnerabilitiesStore ).isQuerying( 'autoPatched' ),
	} ), [] );
	const { query } = useDispatch( vulnerabilitiesStore );

	useEffect( () => {
		query( 'autoPatched', patchableQuery );
	}, [ query ] );

	const numberOfVulnerabilities = items.length;
	return (
		<Page>
			{ ! isQuerying && (
				<>
					<CustomerPatchstackStatusBanner
						hasPatchstack={ hasPatchstack }
						isSmall={ isSmall }
						numberOfVulnerabilities={ numberOfVulnerabilities }
						installType={ installType }
						isMedium={ isMedium }
					/>
					<StyledTableCardContainer isSmall={ isSmall }>
						<StyledTableContainer variant="primary">
							<AutomatedVulnerabilityTableHeader
								hasPatchstack={ hasPatchstack }
								isLarge={ isLarge }
								numberOfVulnerabilities={ numberOfVulnerabilities }
								isMedium={ isMedium }
							/>
							<AutomatedVulnerabilityTable
								items={ items }
								isSmall={ isSmall }
								installType={ installType }
								hasPatchstack={ hasPatchstack }
							/>
						</StyledTableContainer>
						<StyledColumnContainer>
							<InstantProtectionCard
								hasPatchstack={ hasPatchstack }
								numberOfVulnerabilities={ numberOfVulnerabilities }
							/>
							<RealTimeUpdatesCard installType={ installType } />
						</StyledColumnContainer>
					</StyledTableCardContainer>
				</>
			) }
		</Page>
	);
}

function CustomerPatchstackStatusBanner( { hasPatchstack, isSmall, numberOfVulnerabilities, installType, isMedium } ) {
	const [ isDismissed, setIsDismissed ] = useLocalStorage( 'itsecFWAutoHasPatchstack' );

	if ( isDismissed ) {
		return null;
	}

	const headingText = hasPatchstack
		? __( 'Thanks for valuing your site security!', 'better-wp-security' )
		: __( 'Interested in always-on, automated firewall protection?', 'better-wp-security' );

	let subText = '';

	if ( hasPatchstack ) {
		subText = __( 'You’re getting the best protection available with our Patchstack integration.', 'better-wp-security' );
	} else if ( numberOfVulnerabilities > 0 ) {
		subText = sprintf(
			/* translators: 1. Number of vulnerabilities. */
			_n(
				'Solid Security Pro could have instantly protected you from %d vulnerability in the last 90 days using automated firewall protection from Patchstack.',
				'Solid Security Pro could have instantly protected you from %d vulnerabilities in the last 90 days using automated firewall protection from Patchstack.',
				numberOfVulnerabilities,
				'better-wp-security'
			),
			numberOfVulnerabilities
		);
	}

	return (
		<StyledAutomatedBannerSurface
			variant={ hasPatchstack ? 'primary' : 'dark' }
			isSmall={ isSmall }
		>
			<StyledGridContainer isMedium={ isMedium }>
				<Flex direction="column" expanded={ false } justify="center" gap={ 3 }>
					<StyledBannerTitle
						as="h3"
						className={ hasPatchstack ? '' : 'itsec-basic-banner-title' }
						text={ headingText }
						icon={ hasPatchstack ? PurpleShield : BasicToProShield }
						size={ TextSize.LARGE }
						iconSize={ 25 }
					/>
					{ subText && (
						<Text
							as="p"
							text={ subText }
							variant={ hasPatchstack ? TextVariant.DARK : TextVariant.WHITE }
							isSmall={ isSmall }
						/>
					) }
				</Flex>
				{ hasPatchstack && ! isSmall && (
					<>
						<StyledBadge
							text={ __( 'Automated Firewall Active', 'better-wp-security' ) }
							icon={ checkIcon }
							iconColor="#5FB370"
							isMedium={ isMedium }
							align={ isMedium ? 'left' : 'right' }
						/>
						<StyledHasPatchstackDismiss
							label={ __( 'Dismiss', 'better-wp-security' ) }
							icon={ dismissIcon }
							onClick={ () => setIsDismissed( true ) }
						/>
					</>
				) }
				{ ! hasPatchstack && (
					<StyledButton
						text={ installType === 'free'
							? __( 'Get Solid Security Pro + Patchstack', 'better-wp-security' )
							: __( 'Upgrade to Patchstack', 'better-wp-security' )
						}
						variant="primary"
						href={ installType === 'free'
							? 'https://go.solidwp.com/patchstack-banner-upgrade-now'
							: 'https://go.solidwp.com/enable-patchstack'
						}
						icon={ externalIcon }
						iconPosition="right"
						align="right"
						singleColumn={ isMedium }
					/>
				) }
			</StyledGridContainer>
			{ hasPatchstack && isSmall && (
				<>
					<Badge
						text={ __( 'Automated Firewall Active', 'better-wp-security' ) }
						icon={ checkIcon }
						iconColor="#5FB370"
					/>
					<StyledHasPatchstackDismiss
						label={ __( 'Dismiss', 'better-wp-security' ) }
						icon={ dismissIcon }
						onClick={ () => setIsDismissed( true ) }
					/>
				</>
			) }
		</StyledAutomatedBannerSurface>
	);
}

function AutomatedVulnerabilityTableHeader( { hasPatchstack, isLarge, numberOfVulnerabilities, isMedium } ) {
	let headerTitle = '';
	if ( hasPatchstack ) {
		headerTitle = createInterpolateElement(
			sprintf(
				/* translators: 1.Number of vulnerabilities. */
				_n(
					'Solid Security Pro with Patchstack automatically protected you from <b>%d</b> vulnerability in the last 90 days',
					'Solid Security Pro with Patchstack automatically protected you from <b>%d</b> vulnerabilities in the last 90 days',
					numberOfVulnerabilities,
					'better-wp-security'
				), numberOfVulnerabilities
			), { b: <span className="itsec-header-title-small" /> }
		);
	} else {
		headerTitle = createInterpolateElement(
			sprintf(
				/* translators: 1. Number of Vulnerabilities. */
				_n(
					'<b>%d</b> Vulnerability we could have patched automatically',
					'<b>%d</b> Vulnerabilities we could have patched automatically',
					numberOfVulnerabilities,
					'better-wp-security'
				), numberOfVulnerabilities
			), { b: <span className="itsec-header-title-large" /> }
		);
	}

	return (
		<StyledAutomatedVulnerabilityTableHeader variant="primary" isMedium={ isMedium }>
			<StyledVulnerabilityTableHeaderText
				as="h3"
				size={ TextSize.LARGE }
				text={ headerTitle }
				hasPatchstack={ hasPatchstack }
			/>
			<StyledBrand isMedium={ isMedium }>
				<StyledLogoText weight={ 600 } text={ __( 'Powered by', 'better-wp-security' ) } />
				<StyledLogoImage isLarge={ isLarge } />
			</StyledBrand>
		</StyledAutomatedVulnerabilityTableHeader>
	);
}

function AutomatedVulnerabilityTable( { items, isSmall, installType, hasPatchstack } ) {
	return (
		<StyledTableSection as="section">
			{ items.length > 0 && (
				<StyledTable>
					<thead>
						<tr>
							<StyledThead
								as="th"
								text={ __( 'Type', 'better-wp-security' ) }
								textTransform="uppercase"
								weight={ 400 }
							/>
							<StyledThead
								as="th"
								text={ __( 'Vulnerability', 'better-wp-security' ) }
								textTransform="uppercase"
								weight={ 400 }
							/>
							<StyledThead
								as="th"
								text={ __( 'Severity', 'better-wp-security' ) }
								textTransform="uppercase"
								weight={ 400 }
								align="center"
							/>
						</tr>
					</thead>
					<tbody>
						{ items.map( ( vulnerability ) => {
							const id = vulnerability.details.id;
							return (
								<tr key={ id }>
									<>
										<td><StyledVulnerabilityIcon icon={ vulnerabilityIcon( vulnerability.software.type.slug ) } /></td>
										<td>
											<StyledCombinedColumns isSmall={ isSmall }>
												<StyledVulnerabilityName weight={ 500 } text={ vulnerability.software.label || vulnerability.software.slug } />
												<StyledVulnerabilityVersion text={ vulnerability.details.affected_in } />
												{ ! isSmall && (
													<StyledVulnerabilityDetail text={ vulnerability.details.type.label } />
												) }
											</StyledCombinedColumns>
										</td>
										<td>
											<StyledSeverity
												backgroundColor={ severityColor( vulnerability.details.score ) }
												status={ vulnerability.status }
												weight={ 600 }
												text={ vulnerability.details.score ?? '??' }
											/>
										</td>
									</>
								</tr>
							);
						} ) }
					</tbody>
				</StyledTable>
			) }
			{ items.length === 0 && ! hasPatchstack && (
				<StyledNoVulnerabilitiesContainer>
					<ProPlusPatchstack />
					<Text
						text={ createInterpolateElement(
							__( 'We didn’t spot vulnerabilities that could have been patched automatically in the last 90 days, <b>but they can still appear any day</b>. Solid Security Pro with Patchstack gives peace of mind and proactive security upgrade to be automatically protected today!', 'better-wp-security' ),
							{ b: <strong /> }
						) }
						align="center"
					/>
					<StyledNoVulnerabilitiesButton
						variant="text"
						text={ __( 'Go Pro with Patchstack', 'better-wp-security' ) }
						href={ installType === 'free'
							? 'https://go.solidwp.com/patchstack-banner-upgrade-now'
							: 'https://go.solidwp.com/enable-patchstack'
						}
					/>
				</StyledNoVulnerabilitiesContainer>
			) }
			{ items.length === 0 && hasPatchstack && (
				<StyledNoVulnerabilitiesContainer>
					<HiResIcon icon={ <VulnerabilitySuccess /> } />
					<Text
						text={ __( 'We didn’t spot vulnerabilities that could have been patched automatically in the last 90 days, great job keeping your site secure!', 'better-wp-security' ) }
						align="center"
					/>
				</StyledNoVulnerabilitiesContainer>
			) }
		</StyledTableSection>
	);
}

function InstantProtectionCard( { hasPatchstack, numberOfVulnerabilities } ) {
	const cardHeaderText = hasPatchstack
		? __( 'Instant Protection', 'better-wp-security' )
		: __( 'Get Instant Protection', 'better-wp-security' );

	let cardText = '';

	if ( hasPatchstack ) {
		cardText = createInterpolateElement(
			sprintf(
				/* translators: 1. Number of vulnerabilities. */
				_n( 'You were automatically protected from <b>%d vulnerability</b> using <b>virtual patching,</b> Solid Security Pro’s instant protection feature!',
					'You were automatically protected from <b>%d vulnerabilities</b> using <b>virtual patching,</b> Solid Security Pro’s instant protection feature!',
					numberOfVulnerabilities,
					'better-wp-security'
				),
				numberOfVulnerabilities ),
			{ b: <strong /> } );
	} else {
		cardText = createInterpolateElement(
			sprintf(
				/* translators: 1. Number of vulnerabilities. */
				_n( 'Automated protection was available for <b>%d vulnerabilities</b> using <b>virtual patches</b> that instantly deploy when using Solid Security Pro with Patchstack integration. Get ’round the clock protection. Go Pro today.',
					'Automated protection was available for <b>%d vulnerabilities</b> using <b>virtual patches</b> that instantly deploy when using Solid Security Pro with Patchstack integration. Get ’round the clock protection. Go Pro today.',
					numberOfVulnerabilities,
					'better-wp-security',
				),
				numberOfVulnerabilities ),
			{ b: <strong /> } );
	}

	return (
		<StyledAutomatedCardSurface variant="primary">
			<Flex>
				<StyledAutomatedCardHeader
					as="h4"
					text={ cardHeaderText }
					size={ TextSize.SUBTITLE_SMALL }
				/>
				<StyledPatchstackMark />
			</Flex>
			<StyledCardText
				as="p"
				text={ cardText }
				variant={ TextVariant.MUTED }
			/>
			<VirtualPatchingBadge />
		</StyledAutomatedCardSurface>
	);
}

function RealTimeUpdatesCard( { installType } ) {
	const { items } = useSelect( ( select ) => ( {
		items: select( vulnerabilitiesStore ).getQueryResults( 'autoUpdated' ),
	} ), [] );
	const { query } = useDispatch( vulnerabilitiesStore );

	useEffect( () => {
		query( 'autoUpdated', autoUpdatedQuery );
	}, [ query ] );

	const cardHeaderText = installType
		? __( 'Real-time Updates', 'better-wp-security' )
		: __( 'Get Real-time Updates', 'better-wp-security' );

	let cardText = '';
	if ( installType === 'pro' ) {
		cardText = createInterpolateElement(
			sprintf(
				/* translators: 1. Number of vulnerabilities. */
				_n(
					'Solid Security with the help of Patchstack has <b>automatically updated %d plugin or theme</b> in the last 90 days!',
					'Solid Security with the help of Patchstack has <b>automatically updated %d plugins or themes</b> in the last 90 days!',
					items.length,
					'better-wp-security'
				),
				items.length
			),
			{ b: <strong /> }
		);
	} else {
		cardText = createInterpolateElement(
			__( 'Avoid vulnerabilities from harming your site with <b>Real-Time Updates,</b> never forget to upgrade a plugin or theme ever again.', 'better-wp-security' ),
			{ b: <strong /> }
		);
	}

	return (
		<StyledAutomatedCardSurface variant="primary">
			<StyledAutomatedCardHeader
				as="h4"
				text={ cardHeaderText }
				size={ TextSize.SUBTITLE_SMALL }
			/>
			<StyledCardText
				as="p"
				text={ cardText }
				variant={ TextVariant.MUTED }
			/>
			<ActiveUpdatesBadge />
		</StyledAutomatedCardSurface>
	);
}
