/**
 * External dependencies
 */
import { without } from 'lodash';
import { utils } from '@rjsf/core';

/**
 * WordPress dependencies
 */
import { TextareaControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { Markup } from '@ithemes/security-components';

const { getUiOptions } = utils;

export default function TextareaWidget( {
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
	...inputProps
} ) {
	const options = getUiOptions( uiSchema );
	const { rows, placeholder, description = schema.description } = options;

	return (
		<TextareaControl
			value={ typeof value === 'undefined' ? '' : value }
			onChange={ onChange }
			disabled={ disabled }
			readOnly={ readonly }
			label={ label }
			help={ <Markup noWrap content={ description } /> }
			onBlur={ onBlur && ( ( e ) => onBlur( id, e.target.value ) ) }
			onFocus={ onFocus && ( ( e ) => onFocus( id, e.target.value ) ) }
			rows={ rows }
			placeholder={ placeholder }
			{ ...without( inputProps, [
				'autofocus',
				'formContext',
				'registry',
				'rawErrors',
			] ) }
		/>
	);
}
