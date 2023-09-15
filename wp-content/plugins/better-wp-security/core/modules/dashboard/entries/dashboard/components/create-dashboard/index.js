/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { TextControl, Button } from '@wordpress/components';
import { useInstanceId } from '@wordpress/compose';
import { useSelect, useDispatch } from '@wordpress/data';
import { useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import DefaultLayout from './default-layout.svg';
import ScratchLayout from './scratch-layout.svg';
import './style.scss';

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

	const { addDashboard: add } = useDispatch( 'ithemes-security/dashboard' );

	if ( ! canCreate && canCreateLoaded ) {
		return (
			<div className="itsec-create-dashboard">
				<p>
					{ __(
						'You don’t have permission to create new dashboards. Try switching to a dashboard or ask an administrator to invite you to one.',
						'better-wp-security'
					) }
				</p>
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
		<div className="itsec-create-dashboard">
			<section className="itsec-create-dashboard__start itsec-create-dashboard__start--default">
				<header>
					<DefaultLayout height={ 100 } />
					<h2>{ __( 'Start with the default layout.', 'better-wp-security' ) }</h2>
					<p>
						{ __(
							'You can continue to customize this later.',
							'better-wp-security'
						) }
					</p>
				</header>
				<CreateDashboardForm
					label={ defaultLabel }
					onLabelChange={ setDefaultLabel }
					onSubmit={ create( 'default' ) }
					isBusy={ addingDefault }
					isDisabled={ addingScratch }
					hasError={ hasError.default }
				/>
			</section>

			<section className="itsec-create-dashboard__start itsec-create-dashboard__start--scratch">
				<header>
					<ScratchLayout
						height={ 100 }
						className="itsec-create-dashboard__scratch-icon"
					/>
					<h2>{ __( 'Start from Scratch.', 'better-wp-security' ) }</h2>
					<p>
						{ __(
							'Start building a dashboard with security cards.',
							'better-wp-security'
						) }
					</p>
				</header>
				<CreateDashboardForm
					label={ scratchLabel }
					onLabelChange={ setScratchLabel }
					onSubmit={ create( 'scratch' ) }
					isBusy={ addingScratch }
					isDisabled={ addingDefault }
					hasError={ hasError.scratch }
				/>
			</section>
		</div>
	);
}

function CreateDashboardForm( { label, onLabelChange, onSubmit, isDisabled, isBusy, hasError } ) {
	const instanceId = useInstanceId( CreateDashboard );

	return (
		<form onSubmit={ onSubmit }>
			<TextControl
				className={ classnames( 'itsec-create-dashboard__name', {
					'itsec-create-dashboard__name--hide-help-text': ! hasError,
				} ) }
				label={ __( 'Dashboard Name', 'better-wp-security' ) }
				placeholder={ __( 'Dashboard Name…', 'better-wp-security' ) }
				id={ `itsec-create-dashboard__name--${ instanceId }` }
				value={ label }
				onChange={ onLabelChange }
				disabled={ isBusy || isDisabled }
				help={ __( 'Entering a dashboard name is required.', 'better-wp-security' ) }
				required
			/>
			<div className="itsec-create-dashboard__trigger-container">
				<Button
					className="itsec-create-dashboard__trigger"
					type="submit"
					isBusy={ isBusy }
					disabled={ isDisabled }
				>
					{ __( 'Create Board', 'better-wp-security' ) }
				</Button>
			</div>
		</form>
	);
}
