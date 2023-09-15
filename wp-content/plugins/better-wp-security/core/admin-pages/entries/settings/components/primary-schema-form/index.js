/**
 * External dependencies
 */
import { withTheme } from '@rjsf/core';
import { Link } from 'react-router-dom';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { Card, CardBody, Button, Flex, FlexItem } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useInstanceId } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import Theme from '@ithemes/security-rjsf-theme';
import { ErrorList, FlexSpacer } from '@ithemes/security-components';
import { withNavigate } from '@ithemes/security-hocs';
import './style.scss';

const SchemaForm = withTheme( Theme );

export default function PrimarySchemaForm( {
	saveLabel,
	isSaving,
	saveDisabled,
	cancelLabel,
	onCancel,
	cancelRoute,
	undoLabel,
	undoDisabled,
	onUndo,
	children,
	errors,
	apiError,
	schemaError,
	...rest
} ) {
	let id = useInstanceId( PrimarySchemaForm, 'itsec-schema-form' );
	id = rest.id || id;

	return (
		<>
			<Card>
				<CardBody>
					<ErrorList
						errors={ errors }
						apiError={ apiError }
						schemaError={ schemaError }
					/>
					<PrimarySchemaFormInputs { ...rest } id={ id } />
				</CardBody>
			</Card>

			<PrimarySchemaFormActions
				id={ id }
				saveLabel={ saveLabel }
				cancelLabel={ cancelLabel }
				isSaving={ isSaving }
				saveDisabled={ saveDisabled }
				onCancel={ onCancel }
				cancelRoute={ cancelRoute }
				undoLabel={ undoLabel }
				undoDisabled={ undoDisabled }
				onUndo={ onUndo }
			>
				{ children }
			</PrimarySchemaFormActions>
		</>
	);
}

export function PrimarySchemaFormInputs( { className, ...rest } ) {
	return (
		<SchemaForm
			{ ...rest }
			className={ classnames(
				'itsec-primary-schema-form',
				'rjsf',
				className
			) }
			additionalMetaSchemas={ [
				require( 'ajv/lib/refs/json-schema-draft-04.json' ),
			] }
		>
			<></>
		</SchemaForm>
	);
}

export function PrimarySchemaFormActions( {
	id,
	saveLabel = __( 'Save', 'better-wp-security' ),
	isSaving,
	saveDisabled,
	cancelLabel = __( 'Cancel', 'better-wp-security' ),
	onCancel,
	cancelRoute,
	undoLabel = __( 'Undo Changes', 'better-wp-security' ),
	undoDisabled,
	onUndo,
	children,
} ) {
	return (
		<Flex>
			{ onCancel && (
				<FlexItem>
					<Button variant="tertiary" type="button" onClick={ onCancel }>
						{ cancelLabel }
					</Button>
				</FlexItem>
			) }

			{ cancelRoute && (
				<FlexItem>
					<Link
						component={ withNavigate( Button ) }
						variant="tertiary"
						type="button"
						to={ cancelRoute }
					>
						{ cancelLabel }
					</Link>
				</FlexItem>
			) }

			<FlexSpacer />

			{ children }

			{ onUndo && (
				<FlexItem>
					<Button
						variant="secondary"
						disabled={ undoDisabled }
						onClick={ onUndo }
					>
						{ undoLabel }
					</Button>
				</FlexItem>
			) }

			<FlexItem>
				<Button
					variant="primary"
					isBusy={ isSaving }
					disabled={ isSaving || saveDisabled }
					form={ id }
					type="submit"
				>
					{ saveLabel }
				</Button>
			</FlexItem>
		</Flex>
	);
}
