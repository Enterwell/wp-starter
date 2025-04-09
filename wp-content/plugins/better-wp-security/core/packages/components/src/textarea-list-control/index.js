/**
 * External dependencies
 */
import { isArray, isString } from 'lodash';

/**
 * WordPress dependencies
 */
import { TextareaControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

export default function TextareaListControl( { value, onChange, ...props } ) {
	if ( ! isArray( value ) ) {
		value = isString( value ) ? [ value ] : [];
	}

	const [ rawValue, setRawValue ] = useState( value.join( '\n' ) );

	if ( value.join( '\n' ).trim() !== rawValue.trim() ) {
		setRawValue( value.join( '\n' ) );
	}

	const onValueChange = ( v ) => {
		setRawValue( v );
		onChange(
			v
				.split( '\n' )
				.map( ( item ) => item.trim() )
				.filter( ( item ) => item.length > 0 )
		);
	};

	return (
		<TextareaControl
			value={ rawValue }
			onChange={ onValueChange }
			{ ...props }
		/>
	);
}
