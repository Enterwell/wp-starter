/**
 * External dependencies
 */
import { Link, useParams } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useDispatch, useSelect } from '@wordpress/data';
import { dateI18n } from '@wordpress/date';
import { useViewportMatch } from '@wordpress/compose';
import {
	arrowRight,
	chevronLeftSmall,
	external,
	info,
} from '@wordpress/icons';
import { Icon, Tooltip } from '@wordpress/components';
import { createInterpolateElement } from '@wordpress/element';

/**
 * iThemes dependencies
 */
import {
	Button,
	Text,
	TextSize,
	TextVariant,
	TextWeight,
	useConfirmationDialog,
} from '@ithemes/ui';
import { vulnerabilitiesStore } from '@ithemes/security.packages.data';

/**
 * Internal dependencies
 */
import {
	ExploitedIcon,
	UnexploitedIcon,
	VulnerabilitySolved,
} from '@ithemes/security-style-guide';
import { withNavigate } from '@ithemes/security-hocs';
import { BeforeHeaderSlot } from '../../components/before-header';
import {
	ActiveUpdatesBadge,
	VirtualPatchingBadge,
} from '@ithemes/security-ui';
import { StyledPageContainer } from '../../components/styles';
import {
	StyledActions,
	StyledBadgesContainer,
	StyledSurfaceContainer,
	StyledHeader,
	StyledTitle,
	StyledLogo,
	StyledLogoText,
	StyledLogoImage,
	StyledBadgeContainer,
	StyledBadgeSurface,
	StyledBadgeText,
	StyledVulnerabilitySection,
	StyledSectionTitle,
	StyledButtonsContainer,
	StyledDataRow,
	StyledLabelWithIcon,
	StyledInfoIcon,
	StyledTextWithIcon,
	StyledExternalContainer,
	StyledSeverity,
	StyledAlternateInstructions,
} from './styles';

function severityLevel( score ) {
	switch ( true ) {
		case isNaN( score ):
			return __( 'Unknown Severity', 'better-wp-security' );
		case score < 3:
			return __( 'Low Severity', 'better-wp-security' );
		case score < 7:
			return __( 'Medium Severity', 'better-wp-security' );
		case score < 9:
			return __( 'High Severity', 'better-wp-security' );
		default:
			return __( 'Critical Severity', 'better-wp-security' );
	}
}

function severityColor( score ) {
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

function StandardButton( { action, isApplying, onApply } ) {
	return (
		<Button
			isBusy={ isApplying }
			onClick={ onApply }
			variant={ action.rel === 'ithemes-security:fix-vulnerability' ? 'primary' : 'secondary' }
			text={ action.title }
		/>
	);
}

function DestructiveButton( { action, isApplying, onApply } ) {
	const confirmationArgs = {
		title: __( 'Confirm your action', 'better-wp-security' ),
		body: __( 'Are you sure you want to do this?', 'better-wp-security' ),
		onContinue: onApply,
		continueText: action.title,
	};
	const [ onClick, element ] = useConfirmationDialog( confirmationArgs );
	return (
		<>
			<Button
				isDestructive={ action.isDestructive }
				isBusy={ isApplying }
				onClick={ onClick }
				text={ action.title }
			/>
			{ element }
		</>
	);
}

function VulnerabilityAction( { action, vulnerability } ) {
	const { applyVulnerabilityAction } = useDispatch( vulnerabilitiesStore );
	const { isApplying } = useSelect( ( select ) => ( {
		isApplying: select( vulnerabilitiesStore ).isApplyingAction( vulnerability, action.rel ),
	} ), [ action.rel, vulnerability ] );

	const onApply = () => {
		applyVulnerabilityAction( vulnerability, action.rel );
	};

	return (
		action.isDestructive
			? ( <DestructiveButton action={ action } isApplying={ isApplying } onApply={ onApply } /> )
			: ( <StandardButton action={ action } isApplying={ isApplying } onApply={ onApply } /> )
	);
}

function VulnerabilitySolution( { vulnerability, vulnerabilityActions } ) {
	if ( [ 'updated', 'auto-updated', 'deleted' ].includes( vulnerability.resolution.slug ) ) {
		return null;
	}

	return (
		<StyledVulnerabilitySection>
			<StyledSectionTitle
				level={ 3 }
				size={ TextSize.EXTRA_LARGE }
				variant={ TextVariant.DARK }
				text={ __( 'Solution', 'better-wp-security' ) }
			/>
			{ vulnerability.details.fixed_in && (
				<Text
					variant={ TextVariant.DARK }
					weight={ 600 }
					text={ sprintf(
						/* translators: 1. Vulnerability name 2. Vulnerability type. 3. Fixed version  */
						__( 'Update the %1$s %2$s to the latest available version (at least %3$s).', 'better-wp-security' ),
						vulnerability.software.label,
						vulnerability.software.type.slug,
						vulnerability.details.fixed_in ) }
				/>
			) }
			{ ! vulnerability.details.fixed_in && (
				<Text
					variant={ TextVariant.DARK }
					weight={ 600 }
					text={ __( 'No fix has been released for this vulnerability.', 'better-wp-security' ) }
				/>
			) }
			{
				! vulnerabilityActions.find( ( { rel } ) => rel === 'ithemes-security:fix-vulnerability' ) && (
					<StyledAlternateInstructions
						as="p"
						size={ TextSize.SMALL }
						variant={ TextVariant.DARK }
						text={
							<>
								{ vulnerability.software.type.slug === 'plugin' && (
									__( 'If no update is available, you should deactivate the plugin.', 'better-wp-security' ) + ' '
								) }
								{ vulnerability.software.type.slug === 'theme' && (
									__( 'If no update is available, you should switch themes.', 'better-wp-security' ) + ' '
								) }
								{ vulnerabilityActions.find( ( { rel } ) => rel === 'ithemes-security:mute-vulnerability' ) && (
									createInterpolateElement(
										__( 'Muting the issue will exclude it from future scans. <b>Only mute the issue after you’ve confirmed the vulnerability does not affect your site.</b>', 'better-wp-security' ),
										{
											b: <strong />,
										}
									)
								) }
							</>
						}
					/>
				)
			}
			{ vulnerabilityActions.length > 0 && (
				<StyledButtonsContainer>
					{ vulnerabilityActions.map( ( action ) => (
						<VulnerabilityAction key={ action.rel } action={ action } vulnerability={ vulnerability } />
					) ) }
				</StyledButtonsContainer>
			) }
		</StyledVulnerabilitySection>
	);
}

export default function Vulnerability() {
	const { id } = useParams();
	const {
		vulnerability,
		vulnerabilityActions,
	} = useSelect( function( select ) {
		const _vulnerability = select( vulnerabilitiesStore ).getVulnerabilityById( id );
		const _vulnerabilityActions = select( vulnerabilitiesStore ).getVulnerabilityActions( _vulnerability );
		return {
			vulnerability: _vulnerability,
			vulnerabilityActions: _vulnerabilityActions,
		};
	}, [ id ] );
	const isSmall = useViewportMatch( 'small', '<' );
	const isLarge = useViewportMatch( 'large' );

	return (
		<StyledPageContainer>
			<BeforeHeaderSlot />
			<StyledActions isSmall={ isSmall }>
				<Link
					to="/active"
					component={ withNavigate( Button ) }
					icon={ chevronLeftSmall }
					variant="tertiary"
					text={ __( 'Back to Vulnerabilities', 'better-wp-security' ) }
				/>
				<StyledBadgesContainer isSmall={ isSmall }>
					<ActiveUpdatesBadge />
					<VirtualPatchingBadge />
				</StyledBadgesContainer>
			</StyledActions>

			{ vulnerability &&
				<>
					<StyledSurfaceContainer>
						<StyledHeader isLarge={ isLarge }>
							<StyledTitle
								isSmall={ isSmall }
								level={ 2 }
								size={ isSmall ? TextSize.EXTRA_LARGE : TextSize.GIGANTIC }
								variant={ TextVariant.DARK }
								text={ vulnerability.details.title }
							/>
							<StyledLogo isLarge={ isLarge }>
								<StyledLogoText
									variant={ TextVariant.DARK }
									weight={ 600 }
									text={ __( 'Powered by', 'better-wp-security' ) } />
								<StyledLogoImage isLarge={ isLarge } />
							</StyledLogo>
						</StyledHeader>

						<StyledBadgeContainer isLarge={ isLarge }>
							<StyledBadgeSurface variant="tertiary">
								<StyledSeverity color={ severityColor( vulnerability.details.score ) } size={ TextSize.GIGANTIC } text={ vulnerability.details.score ?? '??' } />
								<StyledBadgeText>
									<Text size={ TextSize.LARGE } weight={ 600 } text={ severityLevel( vulnerability.details.score ) } />
									<Text variant={ TextVariant.MUTED } text={
										__( 'CVSS 3.1 score', 'better-wp-security' )
									} />
								</StyledBadgeText>
							</StyledBadgeSurface>
							<StyledBadgeSurface variant="tertiary">
								{ vulnerability.details.is_exploited ? <ExploitedIcon />
									: <UnexploitedIcon />
								}
								<StyledBadgeText>
									<Text
										size={ TextSize.LARGE }
										weight={ 600 }
										text={ vulnerability.details.is_exploited
											? __( 'Exploited Vulnerability', 'better-wp-security' )
											: __( 'Not Known to be Exploited', 'better-wp-security' )
										}
									/>
									<a href="https://patchstack.com/database/report">{ __( 'Report an attack', 'better-wp-security' ) }</a>
								</StyledBadgeText>
							</StyledBadgeSurface>
							{ vulnerability.resolution.slug === 'patched' && (
								<StyledBadgeSurface variant="tertiary">
									<VulnerabilitySolved />
									<Text size={ TextSize.LARGE } weight={ 600 } text={ __( 'Patched Automatically', 'better-wp-security' ) } />
								</StyledBadgeSurface>
							) }
						</StyledBadgeContainer>

						<VulnerabilitySolution vulnerability={ vulnerability } vulnerabilityActions={ vulnerabilityActions } />

						{ vulnerability.resolution.description && (
							<StyledVulnerabilitySection>
								<StyledSectionTitle
									level={ 3 }
									size={ TextSize.EXTRA_LARGE }
									variant={ TextVariant.DARK }
									text={ __( 'Status', 'better-wp-security' ) }
								/>
								<Text
									variant={ TextVariant.DARK }
									text={ vulnerability.resolution.description }
								/>
							</StyledVulnerabilitySection>
						) }

						<StyledVulnerabilitySection>
							<StyledSectionTitle
								level={ 3 }
								size={ TextSize.EXTRA_LARGE }
								variant={ TextVariant.DARK }
								text={ __( 'Details', 'better-wp-security' ) }
							/>
							<Text text={ vulnerability.details.description } />
						</StyledVulnerabilitySection>
					</StyledSurfaceContainer>

					<StyledSurfaceContainer>
						{ vulnerability.software.type.slug !== 'wordpress' &&
						<StyledDataRow isSmall={ isSmall }>
							<Text
								size={ TextSize.LARGE }
								variant={ TextVariant.MUTED }
								weight={ TextWeight.HEAVY }
								text={ __( 'Software', 'better-wp-security' ) }
							/>
							<Text
								size={ TextSize.LARGE }
								variant={ TextVariant.DARK }
								weight={ TextWeight.HEAVY }
								text={ vulnerability.software.label || vulnerability.software.slug }
							/>
						</StyledDataRow>
						}
						<StyledDataRow isSmall={ isSmall }>
							<Text
								size={ TextSize.LARGE }
								variant={ TextVariant.MUTED }
								weight={ TextWeight.HEAVY }
								text={ __( 'Type', 'better-wp-security' ) } />
							<Text
								size={ TextSize.LARGE }
								variant={ TextVariant.DARK }
								weight={ TextWeight.HEAVY }
								text={ vulnerability.software.type.label }
							/>
						</StyledDataRow>
						<StyledDataRow isSmall={ isSmall }>
							<Text
								size={ TextSize.LARGE }
								variant={ TextVariant.MUTED }
								weight={ TextWeight.HEAVY }
								text={ __( 'Vulnerable Versions', 'better-wp-security' ) }
							/>
							<Text
								size={ TextSize.LARGE }
								variant={ TextVariant.DARK }
								weight={ TextWeight.HEAVY }
								text={ vulnerability.details.affected_in }
							/>
						</StyledDataRow>

						{ vulnerability.details.references.filter( ( ref ) => ref.slug !== 'patchstack' )
							.map( ( ref ) => {
								return (
									<StyledDataRow key={ ref.slug } isSmall={ isSmall }>
										<Text
											size={ TextSize.LARGE }
											variant={ TextVariant.MUTED }
											weight={ TextWeight.HEAVY }
											text={ ref.label }
										/>
										<a href={ ref.refs[ 0 ].link }>
											<StyledTextWithIcon>
												<Text
													size={ TextSize.LARGE }
													variant={ TextVariant.DARK }
													weight={ TextWeight.HEAVY }
													text={ ref.label === 'CVE' ? `CVE-${ ref.refs[ 0 ].value }` : ref.refs[ 0 ].value }
												/>
												<Icon icon={ arrowRight } size={ 16 } />
											</StyledTextWithIcon>
										</a>
									</StyledDataRow>
								);
							} )
						}

						<StyledDataRow isSmall={ isSmall }>
							<StyledLabelWithIcon>
								<Text
									size={ TextSize.LARGE }
									variant={ TextVariant.MUTED }
									weight={ TextWeight.HEAVY }
									text={ __( 'Classification', 'better-wp-security' ) }
								/>
								<Tooltip text={ __( 'Vulnerability Type', 'better-wp-security' ) }>
									<span><StyledInfoIcon icon={ info } /></span>
								</Tooltip>
							</StyledLabelWithIcon>
							<Text
								size={ TextSize.LARGE }
								variant={ TextVariant.DARK }
								weight={ TextWeight.HEAVY }
								text={ vulnerability.details.type.label }
							/>
						</StyledDataRow>
						<StyledDataRow isLastRow isSmall={ isSmall }>
							<Text
								size={ TextSize.LARGE }
								variant={ TextVariant.MUTED }
								weight={ TextWeight.HEAVY }
								text={ __( 'Publicly Disclosed', 'better-wp-security' ) }
							/>
							<Text
								size={ TextSize.LARGE }
								variant={ TextVariant.DARK }
								weight={ TextWeight.HEAVY }
								text={ dateI18n( 'F j, Y', vulnerability.details.published_at ) }
							/>
						</StyledDataRow>
						<StyledExternalContainer>
							<Button href={ vulnerability.details.references[ 0 ].refs[ 0 ].link } icon={ external } iconPosition="right" variant="link" text={ __( 'Vulnerability Details' ) } />
						</StyledExternalContainer>
					</StyledSurfaceContainer>
				</>
			}
		</StyledPageContainer>
	);
}
