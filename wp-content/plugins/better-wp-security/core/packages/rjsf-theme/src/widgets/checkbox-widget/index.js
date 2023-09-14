/**
 * External dependencies
 */
import { utils } from '@rjsf/core';

/**
 * WordPress dependencies
 */
import { CheckboxControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { Markup } from '@ithemes/security-components';

export default function CheckboxWidget( {
	schema,
	uiSchema = {},
	id,
	value,
	disabled,
	readonly,
	label,
	onBlur,
	onFocus,
	onChange,
} ) {
	const required = utils.schemaRequiresTrueValue( schema );
	const description = uiSchema[ 'ui:description' ] || schema.description;

	return (
		<CheckboxControl
			checked={ value || false }
			onChange={ onChange }
			required={ required }
			disabled={ disabled }
			readOnly={ readonly }
			label={ label }
			help={ <Markup noWrap content={ description } /> }
			onBlur={ onBlur && ( ( e ) => onBlur( id, e.target.checked ) ) }
			onFocus={ onFocus && ( ( e ) => onFocus( id, e.target.checked ) ) }
		/>
	);
}
