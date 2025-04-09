/**
 * External dependencies
 */
import { cloneDeep } from 'lodash';

/**
 * WordPress dependencies
 */
import { useInstanceId, useRefEffect } from '@wordpress/compose';
import { useSelect } from '@wordpress/data';
import { useMemo } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import { appendClassNameAtPath } from '../../utils';
import {
	StyledErrorList,
	StyledPrimarySchemaFormInputs,
} from './styles';

export default function ModuleFormInputs( {
	module,
	schema,
	uiSchema: uiSchemaRaw,
	formData,
	setFormData,
	highlightedSetting,
} ) {
	const id = useInstanceId(
		ModuleFormInputs,
		`itsec-configure-${ module.id }`
	);

	const { apiError } = useSelect(
		( select ) => ( {
			apiError: select( MODULES_STORE_NAME ).getError( module.id ),
		} ),
		[ module.id ]
	);

	const uiSchema = useMemo( () => {
		if ( ! highlightedSetting ) {
			return uiSchemaRaw;
		}

		return appendClassNameAtPath(
			uiSchemaRaw ? cloneDeep( uiSchemaRaw ) : {},
			[ highlightedSetting, 'classNames' ],
			'itsec-highlighted-search-result'
		);
	}, [ uiSchemaRaw, highlightedSetting ] );

	const formContext = useMemo(
		() => ( {
			module: module.id,
			disableInlineErrors: true,
		} ),
		[ module.id ]
	);

	const ref = useRefEffect( ( form ) => {
		if ( highlightedSetting && form?.formElement ) {
			window.requestAnimationFrame( () => {
				form.formElement.querySelector( '.itsec-highlighted-search-result' )?.scrollIntoView( {
					behavior: 'smooth',
				} );
			} );
		}
	}, [ highlightedSetting ] );

	return (
		<>
			<StyledErrorList apiError={ apiError } />
			<StyledPrimarySchemaFormInputs
				ref={ ref }
				tagName="div"
				id={ id }
				schema={ schema }
				uiSchema={ uiSchema }
				formData={ formData }
				onChange={ setFormData }
				idPrefix={ `itsec_${ module.id }` }
				formContext={ formContext }
				showErrorList={ false }
			/>
		</>
	);
}
