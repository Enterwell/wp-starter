/**
 * External dependencies
 */
import { Link } from 'react-router-dom';

/**
 * WordPress dependencies
 */
import { Card, CardBody, Flex, FlexItem } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useInstanceId } from '@wordpress/compose';
import { forwardRef } from '@wordpress/element';

/**
 * iThemes dependencies
 */
import { Button } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { withNavigate } from '@ithemes/security-hocs';
import { ErrorList, FlexSpacer } from '@ithemes/security-ui';
import { StyledSchemaForm } from './styles';

export function PrimarySchemaForm( {
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
	withCard,
	alignActions,
	...rest
} ) {
	let id = useInstanceId( PrimarySchemaForm, 'itsec-schema-form' );
	id = rest.id || id;

	const form = (
		<>
			<ErrorList
				errors={ errors }
				apiError={ apiError }
				schemaError={ schemaError }
			/>
			<PrimarySchemaFormInputs { ...rest } id={ id } />
		</>
	);

	return (
		<Flex direction="column" gap={ 7 } justify="start" expanded={ false }>
			<FlexItem>
				{ withCard && (
					<Card>
						<CardBody>
							{ form }
						</CardBody>
					</Card>
				) }

				{ ! withCard && form }
			</FlexItem>

			<FlexItem>
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
					align={ alignActions }
				>
					{ children }
				</PrimarySchemaFormActions>
			</FlexItem>
		</Flex>
	);
}

export const PrimarySchemaFormInputs = forwardRef( ( { className, ...rest }, ref ) => {
	return (
		<StyledSchemaForm
			{ ...rest }
			ref={ ref }
			className={ className }
			additionalMetaSchemas={ [
				require( 'ajv/lib/refs/json-schema-draft-04.json' ),
			] }
		>
			<></>
		</StyledSchemaForm>
	);
} );

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
	align,
	children,
} ) {
	return (
		<Flex align={ align }>
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

			{ ! align && ( <FlexSpacer /> ) }

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
