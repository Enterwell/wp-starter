/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useInstanceId } from '@wordpress/compose';
import { useSelect, useDispatch } from '@wordpress/data';
import { useState } from '@wordpress/element';

/**
 * SolidWP dependencies
 */
import {
	Button,
	Text,
	TextSize,
	TextVariant,
} from '@ithemes/ui';

/**
 * Internal dependencies
 */
import {
	DashboardIcon,
	StyledModal,
	StyledDashboardHeading,
	StyledContainer,
	StyledDashboard,
	StyledDefaultDashboard,
	StyledHeader,
	StyledForm,
	StyledTextControl,
	StyledHelpText,
} from './styles';

export default function CreateDashboard() {
	const [ defaultLabel, setDefaultLabel ] = useState( '' );
	const [ scratchLabel, setScratchLabel ] = useState( '' );
	const [ hasError, setHasError ] = useState( {} );

	const { canCreate, canCreateLoaded, addingScratch, addingDefault } = useSelect( ( select ) => ( {
		canCreate: select( 'ithemes-security/dashboard' ).canCreateDashboards(),
		canCreateLoaded: select(
			'ithemes-security/dashboard'
		).isCanCreateDashboardsLoaded(),
		addingScratch: select( 'ithemes-security/dashboard' ).isAddingDashboard(
			'create-dashboard-scratch'
		),
		addingDefault: select( 'ithemes-security/dashboard' ).isAddingDashboard(
			'create-dashboard-default'
		),
	} ) );

	const { addDashboard: add, viewPrevious } = useDispatch( 'ithemes-security/dashboard' );
	if ( ! canCreate && canCreateLoaded ) {
		return (
			<div className="itsec-create-dashboard">
				<Text as="p" text={ __(
					'You don’t have permission to create new dashboards. Try switching to a dashboard or ask an administrator to invite you to one.',
					'better-wp-security'
				) } />
			</div>
		);
	}

	const create = ( type ) => ( e ) => {
		e.preventDefault();

		const dashboard = {};
		switch ( type ) {
			case 'scratch':
				dashboard.label = scratchLabel;
				break;
			case 'default':
				dashboard.label = defaultLabel;
				dashboard.preset = 'default';
				break;
			default:
				return;
		}
		if ( dashboard.label.trim().length <= 0 ) {
			setHasError( { [ type ]: true } );
			return;
		}

		add( dashboard, `create-dashboard-${ type }` );
	};

	return (
		<StyledModal className="itsec-apply-css-vars" onRequestClose={ viewPrevious } title={ __( 'Create a New Dashboard', 'better-wp-security' ) }>
			<StyledContainer>
				<StyledDefaultDashboard>
					<StyledHeader>
						<DashboardIcon type="default" />
						<StyledDashboardHeading
							align="center"
							level={ 2 }
							size={ TextSize.LARGE }
							variant={ TextVariant.DARK }
							weight={ 600 }
							text={ __( 'Start with the default layout', 'better-wp-security' ) }
						/>
						<Text
							align="center"
							as="p"
							size={ TextSize.SMALL }
							variant={ TextVariant.MUTED }
							text={ __(
								'You can continue to customize this later.',
								'better-wp-security'
							) }
						/>
					</StyledHeader>
					<CreateDashboardForm
						label={ defaultLabel }
						onLabelChange={ setDefaultLabel }
						onSubmit={ create( 'default' ) }
						isBusy={ addingDefault }
						isDisabled={ addingScratch }
						hasError={ hasError.default }
						buttonText={ __( 'Create board with the default layout', 'better-wp-security' ) }
					/>
				</StyledDefaultDashboard>

				<StyledDashboard className="itsec-create-dashboard__start itsec-create-dashboard__start--scratch">
					<StyledHeader>
						<DashboardIcon type="scratch" />
						<StyledDashboardHeading
							align="center"
							level={ 2 }
							size={ TextSize.LARGE }
							variant={ TextVariant.DARK }
							weight={ 600 }
							text={ __( 'Start from scratch', 'better-wp-security' ) }
						/>
						<Text
							align="center"
							as="p"
							size={ TextSize.SMALL }
							variant={ TextVariant.MUTED }
							text={ __(
								'Start building a dashboard with security cards.',
								'better-wp-security' ) }
						/>
					</StyledHeader>
					<CreateDashboardForm
						label={ scratchLabel }
						onLabelChange={ setScratchLabel }
						onSubmit={ create( 'scratch' ) }
						isBusy={ addingScratch }
						isDisabled={ addingDefault }
						hasError={ hasError.scratch }
						buttonText={ __( 'Create board from scratch', 'better-wp-security' ) }
					/>
				</StyledDashboard>
			</StyledContainer>
		</StyledModal>
	);
}

function CreateDashboardForm( { label, onLabelChange, buttonText, onSubmit, isDisabled, isBusy, hasError } ) {
	const instanceId = useInstanceId( CreateDashboard );
	return (
		<StyledForm onSubmit={ onSubmit }>
			<div>
				<StyledTextControl
					hideLabelFromVision
					label={ __( 'Dashboard Name', 'better-wp-security' ) }
					placeholder={ __( 'Dashboard Name', 'better-wp-security' ) }
					id={ `itsec-create-dashboard__name--${ instanceId }` }
					value={ label }
					onChange={ onLabelChange }
					disabled={ isBusy || isDisabled }
					required
				/>
				<StyledHelpText as="p" hasError={ hasError } variant={ TextVariant.MUTED } text={ __( 'Entering a dashboard name is required.', 'better-wp-security' ) } />
			</div>
			<Button
				type="submit"
				isBusy={ isBusy }
				disabled={ isDisabled }
				text={ buttonText }
			/>
		</StyledForm>
	);
}
