/**
 * External dependencies
 */
import { without } from 'lodash';

/**
 * WordPress dependencies
 */
import { TextareaControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { Markup } from '@ithemes/security-components';

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
	const description = uiSchema[ 'ui:description' ] || schema.description;

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
			{ ...without( inputProps, [
				'autofocus',
				'formContext',
				'registry',
				'rawErrors',
			] ) }
		/>
	);
}
