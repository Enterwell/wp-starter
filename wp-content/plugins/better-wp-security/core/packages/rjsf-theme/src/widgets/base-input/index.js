/**
 * External dependencies
 */
import { omit } from 'lodash';

/**
 * WordPress dependencies
 */
import { TextControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { Markup } from '@ithemes/security-components';

function BaseInput( props ) {
	const {
		// eslint-disable-next-line no-unused-vars
		id,
		label,
		value,
		readonly,
		disabled,
		onBlur,
		onFocus,
		options,
		onChange,
		schema,
		uiSchema = {},
		...inputProps
	} = props;

	// If options.inputType is set use that as the input type
	if ( options.inputType ) {
		inputProps.type = options.inputType;
	} else if ( ! inputProps.type ) {
		// If the schema is of type number or integer, set the input type to number
		if ( schema.type === 'number' ) {
			inputProps.type = 'number';
			// Setting step to 'any' fixes a bug in Safari where decimals are not
			// allowed in number inputs
			inputProps.step = 'any';
		} else if ( schema.type === 'integer' ) {
			inputProps.type = 'number';
			// Since this is integer, you always want to step up or down in multiples
			// of 1
			inputProps.step = '1';
		} else {
			inputProps.type = 'text';
		}
	}

	if ( options.autocomplete ) {
		inputProps.autoComplete = options.autocomplete;
	}

	// If multipleOf is defined, use this as the step value. This mainly improves
	// the experience for keyboard users (who can use the up/down KB arrows).
	if ( schema.multipleOf ) {
		inputProps.step = schema.multipleOf;
	}

	if ( typeof schema.minimum !== 'undefined' ) {
		inputProps.min = schema.minimum;
	}

	if ( typeof schema.maximum !== 'undefined' ) {
		inputProps.max = schema.maximum;
	}

	const description = uiSchema[ 'ui:description' ] || schema.description;

	return (
		<TextControl
			label={ label }
			help={ <Markup noWrap content={ description } /> }
			readOnly={ readonly }
			disabled={ disabled }
			value={ value ? value : '' }
			{ ...omit( inputProps, [
				'autofocus',
				'formContext',
				'registry',
				'rawErrors',
			] ) }
			onChange={ ( newValue ) =>
				onChange( newValue === '' ? options.emptyValue : newValue )
			}
			onBlur={
				onBlur && ( ( e ) => onBlur( inputProps.id, e.target.value ) )
			}
			onFocus={
				onFocus && ( ( e ) => onFocus( inputProps.id, e.target.value ) )
			}
		/>
	);
}

export default BaseInput;
