/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { ErrorList } from '@ithemes/security-components';
import {
	PrimarySchemaForm,
	PageHeader,
	Breadcrumbs,
} from '../../../components';
import { ONBOARD_STORE_NAME } from '../../../stores';

const formContext = {
	disableInlineErrors: true,
};

export default function Question( {
	prompt,
	description,
	showErrors = true,
	children,
} ) {
	const { error, siteTypeTitle } = useSelect( ( select ) => ( {
		error: select( ONBOARD_STORE_NAME ).getLastError(),
		siteTypeTitle: select( ONBOARD_STORE_NAME ).getSelectedSiteType()
			?.title,
	} ) );

	return (
		<>
			<PageHeader
				title={ prompt }
				subtitle={ description }
				breadcrumbs={ <Breadcrumbs title={ siteTypeTitle } /> }
			/>
			{ showErrors && (
				<ErrorList
					apiError={ error }
					className="itsec-site-type-question__error-list"
				/>
			) }
			{ children }
		</>
	);
}

export function SchemaQuestion( { question, onAnswer, goBack } ) {
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
				cancelLabel={ __( 'Back', 'better-wp-security' ) }
				onCancel={ goBack }
				formContext={ formContext }
				apiError={ error }
				schemaError={ schemaError }
				onError={ setSchemaError }
				showErrorList={ false }
			/>
		</Question>
	);
}
