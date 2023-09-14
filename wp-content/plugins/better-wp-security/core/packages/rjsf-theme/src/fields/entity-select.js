/**
 * External dependencies
 */
import { utils } from '@rjsf/core';

/**
 * Internal dependencies
 */
import { EntitySelectControl } from '@ithemes/security-components';

const { getUiOptions } = utils;

export default function EntitySelectField( {
	uiSchema,
	schema,
	idSchema,
	name,
	formData,
	disabled,
	readonly,
	onChange,
} ) {
	const options = getUiOptions( uiSchema );

	return (
		<EntitySelectControl
			id={ idSchema.$id }
			value={ formData }
			disabled={ disabled }
			readonly={ readonly }
			onChange={ onChange }
			isMultiple={ schema.type === 'array' }
			label={ uiSchema[ 'ui:title' ] || schema.title || name }
			description={ uiSchema[ 'ui:description' ] || schema.description }
			path={ options.path }
			query={ options.query }
			labelAttr={ options.labelAttr }
			idAttr={ options.idAttr }
			searchArg={ options.searchArg }
		/>
	);
}
