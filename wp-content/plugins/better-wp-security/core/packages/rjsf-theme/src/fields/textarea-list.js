/**
 * External dependencies
 */
import { utils } from '@rjsf/core';

/**
 * Internal dependencies
 */
import { TextareaListControl, Markup } from '@ithemes/security-components';

const { getUiOptions } = utils;

export default function TextareaListField( {
	uiSchema,
	schema,
	name,
	formData,
	disabled,
	readonly,
	onChange,
} ) {
	const label = uiSchema[ 'ui:title' ] || schema.title || name;
	const description = uiSchema[ 'ui:description' ] || schema.description;
	const options = getUiOptions( uiSchema );
	const { rows, placeholder } = options;

	return (
		<TextareaListControl
			label={ label }
			help={ <Markup noWrap content={ description } /> }
			disabled={ disabled }
			readonly={ readonly }
			rows={ rows }
			value={ formData }
			onChange={ onChange }
			placeholder={ placeholder }
		/>
	);
}
