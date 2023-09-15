/**
 * External dependencies
 */
import { utils } from '@rjsf/core';

/**
 * WordPress dependencies
 */
import { RadioControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { Markup } from '@ithemes/security-components';

const { asNumber, guessType } = utils;
const nums = new Set( [ 'number', 'integer' ] );

/**
 * This is a silly limitation in the DOM where option change event values are
 * always retrieved as strings.
 *
 * @param {Object} schema
 * @param {string} schema.type
 * @param {Array}  schema.enum
 * @param {*}      value
 *
 * @return {*} The processed value.
 */
function processValue( schema, value ) {
	// "enum" is a reserved word, so only "type" and "items" can be destructured
	const { type, items } = schema;
	if ( value === '' ) {
		return undefined;
	} else if ( type === 'array' && items && nums.has( items.type ) ) {
		return value.map( asNumber );
	} else if ( type === 'boolean' ) {
		return value === 'true';
	} else if ( type === 'number' ) {
		return asNumber( value );
	}

	// If type is undefined, but an enum is present, try and infer the type from
	// the enum values
	if ( schema.enum ) {
		if ( schema.enum.every( ( x ) => guessType( x ) === 'number' ) ) {
			return asNumber( value );
		} else if (
			schema.enum.every( ( x ) => guessType( x ) === 'boolean' )
		) {
			return value === 'true';
		}
	}

	return value;
}

export default function RadioWidget( {
	schema,
	uiSchema = {},
	id,
	options,
	value,
	label,
	required,
	disabled,
	readonly,
	onChange,
	onBlur,
	onFocus,
} ) {
	const { enumOptions } = options;
	const description = uiSchema[ 'ui:description' ] || schema.description;

	return (
		<RadioControl
			selected={ value }
			options={ enumOptions }
			label={ label }
			help={ <Markup noWrap content={ description } /> }
			required={ required }
			disabled={ disabled }
			readOnly={ readonly }
			onChange={ ( newValue ) =>
				onChange( processValue( schema, newValue ) )
			}
			onBlur={
				onBlur &&
				( ( e ) =>
					onBlur( id, processValue( schema, e.target.value ) ) )
			}
			onFocus={
				onFocus &&
				( ( e ) =>
					onFocus( id, processValue( schema, e.target.value ) ) )
			}
		/>
	);
}
