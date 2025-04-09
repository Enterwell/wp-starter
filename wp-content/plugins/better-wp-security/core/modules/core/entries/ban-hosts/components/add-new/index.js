/**
 * External dependencies
 */
import { withTheme } from '@rjsf/core';
import { mapValues, isObject, isEmpty } from 'lodash';

/**
 * WordPress dependencies
 */
import { useMemo, useState, useRef } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';

/**
 * SolidWP dependencies
 */
import { SurfaceVariant } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import Theme from '@ithemes/security-rjsf-theme';
import { modifySchemaByUiSchema } from '@ithemes/security-utils';
import { StyledSectionCreate } from './styles';

const SchemaForm = withTheme( Theme );

const formContext = {
	disableInlineErrors: true,
};

export default function AddNew( {
	id,
	createForm,
	save,
	setSaving,
	afterSave,
} ) {
	const formElement = useRef( null );
	const [ createData, setCreateData ] = useState( {} );
	const [ extraErrors, setExtraErrors ] = useState( {} );
	const { createNotice } = useDispatch( 'core/notices' );
	const createFormSchema = useMemo( () => {
		if ( ! createForm ) {
			return;
		}

		return modifySchemaByUiSchema(
			createForm.submissionSchema,
			createForm.submissionSchema.uiSchema || {}
		);
	}, [ createForm ] );
	const onSubmit = async ( e ) => {
		setSaving( true );
		setExtraErrors( {} );
		const ban = await save( createForm.href, e.formData );
		setSaving( false );

		if ( ban instanceof Error ) {
			if (
				ban.code === 'rest_invalid_param' &&
				isObject( ban.data.params )
			) {
				const invalidParams = mapValues(
					ban.data.params,
					( error ) => ( { __errors: [ error ] } )
				);
				setExtraErrors( invalidParams );
			} else {
				createNotice( 'error', ban.message, {
					context: 'ithemes-security',
				} );
			}

			return;
		}

		afterSave();
		setCreateData( {} );
		if ( formElement && formElement.current ) {
			const firstInput = formElement.current.formElement.querySelector(
				'input'
			);

			if ( firstInput ) {
				firstInput.focus();
			}
		}
	};

	return (
		<StyledSectionCreate
			as="section"
			variant={ SurfaceVariant.SECONDARY }
		>
			{ createFormSchema && (
				<SchemaForm
					id={ id }
					idPrefix={ `${ id }_part` }
					formData={ createData }
					onChange={ ( e ) => setCreateData( e.formData ) }
					onSubmit={ onSubmit }
					schema={ createFormSchema }
					uiSchema={ createFormSchema.uiSchema || {} }
					omitExtraData
					liveValidate={ ! isEmpty( createData ) }
					extraErrors={ extraErrors }
					formContext={ formContext }
					ref={ formElement }
				>
					<></>
				</SchemaForm>
			) }
		</StyledSectionCreate>
	);
}
