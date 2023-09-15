/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { Card, CardBody, Button, Flex, FlexItem } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useInstanceId } from '@wordpress/compose';
import { Children, isValidElement } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { FlexSpacer, ErrorList } from '@ithemes/security-components';
import './style.scss';

export default function PrimaryForm( {
	onSubmit = () => {},
	saveLabel = __( 'Save', 'better-wp-security' ),
	isSaving,
	saveDisabled = false,
	onCancel,
	cancelHref,
	cancelLabel = __( 'Cancel', 'better-wp-security' ),
	id,
	children,
	buttons,
	errors,
	apiError,
} ) {
	const generatedId = useInstanceId( PrimaryForm, 'itsec-primary-form' );
	id = id || generatedId;

	return (
		<>
			<Card className="itsec-primary-form">
				<CardBody>
					<ErrorList errors={ errors } apiError={ apiError } />
					<form
						id={ id }
						onSubmit={ ( e ) => {
							e.preventDefault();
							onSubmit();
						} }
					>
						{ children }
					</form>
				</CardBody>
			</Card>

			<Flex>
				{ ( onCancel || cancelHref ) && (
					<FlexItem>
						<Button
							variant="tertiary"
							onClick={ onCancel }
							href={ cancelHref }
						>
							{ cancelLabel }
						</Button>
					</FlexItem>
				) }

				<FlexSpacer />

				{ buttons &&
					buttons.map( ( button, i ) => (
						<FlexItem key={ i }>{ button }</FlexItem>
					) ) }

				<FlexItem>
					<Button
						variant="primary"
						isBusy={ isSaving }
						disabled={ saveDisabled }
						form={ id }
						type="submit"
					>
						{ saveLabel }
					</Button>
				</FlexItem>
			</Flex>
		</>
	);
}

export function PrimaryFormSection( { heading, className, children } ) {
	if ( ! Children.toArray( children ).some( isValidElement ) ) {
		return null;
	}

	return (
		<div
			className={ classnames( 'itsec-primary-form__section', className ) }
		>
			{ heading && (
				<h3 className="itsec-primary-form__section-title">
					{ heading }
				</h3>
			) }

			{ children }
		</div>
	);
}
