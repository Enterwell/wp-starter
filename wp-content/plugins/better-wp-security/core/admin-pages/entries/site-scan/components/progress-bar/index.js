/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { Tooltip } from '@wordpress/components';

/**
 * SolidWP dependencies
 */
import store from '../../store';

/**
 * Internal dependencies
 */
import { ProgressBarBeforeSlot } from '../slot-fill';
import { coreStore } from '@ithemes/security.packages.data';
import {
	StyledProgressContainer,
	StyledProgressBar,
	StyledIconRow,
	StyledProgressTrack,
	StyledCaret,
	StyledScanComponent,
	StyledAnimatedComponentContainer,
	StyledTextContainer,
	StyledComponentText,
	StyledUpgradeButton,
	StatusIndicator,
	progressBarColor,
} from './styles';

export function ScanComponentPromo( { label, description, index } ) {
	const status = 'upgrade';
	return (
		<StyledScanComponent>
			<StyledIconRow>
				<StyledProgressTrack background={ progressBarColor( status, index, 50, 'left', 'free' ) } />
				<StatusIndicator status={ status } />
				<StyledProgressTrack background={ progressBarColor( status, index, 50, 'right', 'free' ) } />
			</StyledIconRow>
			<Tooltip text={ description }>
				<StyledTextContainer>
					<StyledCaret status={ status } />
					<StyledComponentText status={ status } text={ label } />
				</StyledTextContainer>
			</Tooltip>
			<StyledUpgradeButton
				variant="tertiary"
				href="https://go.solidwp.com/upgrade-to-solid-security-pro"
				target="_blank"
				text={ __( 'Unlock', 'better-wp-security' ) }
			/>
		</StyledScanComponent>
	);
}

function ScanComponent( { slug, index, length, isStep, installType } ) {
	const { component, status, hasIssues } = useSelect( ( select ) => ( {
		component: select( store ).getComponentBySlug( slug ),
		status: select( store ).getScanComponentStatus( slug ),
		hasIssues: select( store ).componentHasIssues( slug ),
	} ), [ slug ] );

	return (
		<StyledScanComponent isStep={ isStep }>
			{ isStep ? (
				<StatusIndicator isStep={ isStep } status={ status } hasIssues={ hasIssues } />
			) : (
				<StyledIconRow>
					<StyledProgressTrack background={ progressBarColor( status, index, length, 'left', installType ) } />
					<StatusIndicator status={ status } hasIssues={ hasIssues } />
					<StyledProgressTrack background={ progressBarColor( status, index, length, 'right', installType ) } />
				</StyledIconRow>
			) }

			<Tooltip text={ component.description }>
				<StyledTextContainer>
					<StyledCaret status={ status } />
					<StyledComponentText status={ status } text={ component.label } />
				</StyledTextContainer>
			</Tooltip>
		</StyledScanComponent>
	);
}

function Overview( { components, isComplete } ) {
	const { installType } = useSelect(
		( select ) => ( {
			installType: select( coreStore ).getInstallType(),
		} ),
		[]
	);

	return (
		<StyledProgressBar isComplete={ isComplete }>
			<ProgressBarBeforeSlot />
			{ components.map( ( component, index ) => (
				<ScanComponent
					key={ component.slug }
					slug={ component.slug }
					index={ index }
					length={ components.length }
					installType={ installType }
				/>
			) ) }
		</StyledProgressBar>
	);
}

function Stepper() {
	const { currentStep, previousStep, nextStep } = useSelect( ( select ) => ( {
		currentStep: select( store ).getCurrentScanComponent(),
		previousStep: select( store ).getPreviousScanComponent(),
		nextStep: select( store ).getUpcomingScanComponent(),
	} ), [] );

	return (
		<StyledAnimatedComponentContainer>
			{ previousStep && <ScanComponent slug={ previousStep } isStep="previous" key={ previousStep } /> }
			{ currentStep && <ScanComponent slug={ currentStep } isStep="current" key={ currentStep } /> }
			{ nextStep && <ScanComponent slug={ nextStep } isStep="next" key={ nextStep } /> }
		</StyledAnimatedComponentContainer>
	);
}

export default function ProgressBar( { components } ) {
	const { isScanRunning, hasCompletedScan } = useSelect( ( select ) => ( {
		isScanRunning: select( store ).isScanRunning(),
		hasCompletedScan: select( store ).hasCompletedScan(),
	} ), [] );
	return (
		<StyledProgressContainer>
			{ ! isScanRunning && <Overview components={ components } isComplete={ hasCompletedScan } /> }
			{ isScanRunning && <Stepper components={ components } /> }
		</StyledProgressContainer>
	);
}
