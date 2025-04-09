/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { arrowLeft as backIcon } from '@wordpress/icons';

/**
 * Solid dependencies
 */
import { Button, Heading, Text, TextSize, TextVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { ErrorList, Markup } from '@ithemes/security-ui';
import { PrimarySchemaForm } from '@ithemes/security-schema-form';
import { ONBOARD_STORE_NAME } from '../../../stores';
import { StyledQuestion, StyledQuestionHeader } from './styles';
import { OnboardBackActionFill } from '../../../components';

const formContext = {
	disableInlineErrors: true,
};

export default function Question( {
	prompt,
	description,
	showErrors = true,
	goBack,
	children,
} ) {
	const { error } = useSelect(
		( select ) => ( {
			error: select( ONBOARD_STORE_NAME ).getLastError(),
		} ),
		[]
	);

	return (
		<>
			<StyledQuestion>
				<StyledQuestionHeader>
					<Heading
						level={ 3 }
						text={ prompt }
						size={ TextSize.EXTRA_LARGE }
						variant={ TextVariant.DARK }
					/>
					<Text
						as="p"
						variant={ TextVariant.MUTED }
					>
						<Markup content={ description } noWrap />
					</Text>
				</StyledQuestionHeader>
				{ showErrors && (
					<ErrorList apiError={ error } />
				) }
				{ children }
			</StyledQuestion>
			{ goBack && (
				<OnboardBackActionFill>
					<Button
						onClick={ goBack }
						text={ __( 'Back', 'better-wp-security' ) }
						icon={ backIcon }
						iconPosition="left"
						variant="tertiary"
					/>
				</OnboardBackActionFill>
			) }
		</>
	);
}

export function SchemaQuestion( { question, onAnswer, isAnswering, goBack } ) {
	const { editAnswer } = useDispatch( ONBOARD_STORE_NAME );
	const { answer, error } = useSelect( ( select ) => ( {
		error: select( ONBOARD_STORE_NAME ).getLastError(),
		answer: select( ONBOARD_STORE_NAME ).getEditedAnswer(),
	} ) );
	const [ schemaError, setSchemaError ] = useState( [] );

	return (
		<Question
			prompt={ question.prompt }
			description={ question.description }
			showErrors={ false }
			goBack={ goBack }
		>
			<PrimarySchemaForm
				schema={ question.answer_schema }
				uiSchema={ question.answer_schema.uiSchema }
				formData={ answer }
				onChange={ ( { formData: changedData } ) =>
					editAnswer( changedData )
				}
				onSubmit={ ( { formData: submittedData }, e ) => {
					e.preventDefault();
					setSchemaError( [] );
					onAnswer( submittedData );
				} }
				saveLabel={ __( 'Next', 'better-wp-security' ) }
				formContext={ formContext }
				apiError={ error }
				schemaError={ schemaError }
				onError={ setSchemaError }
				showErrorList={ false }
				saveDisabled={ isAnswering }
				undoDisabled={ isAnswering }
				alignActions="start"
			/>
		</Question>
	);
}
