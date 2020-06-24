/**
 * External dependencies
 */
import { omit, isArray } from 'lodash';

/**
 * WordPress dependencies
 */
import { CheckboxControl } from '@wordpress/components';

export default function CheckboxGroupControl( { value, onChange, options, label, help } ) {
	let isChecked, update;

	if ( isArray( value ) ) {
		isChecked = ( option ) => value.includes( option.value );
		update = ( option ) => ( checked ) => onChange( checked ? [ ...value, option.value ] : value.filter( ( maybeValue ) => maybeValue !== option.value ) );
	} else {
		isChecked = ( option ) => value[ option.value ] || false;
		update = ( option ) => ( checked ) => onChange( { ...value, [ option.value ]: checked } );
	}

	return (
		<fieldset className="components-base-control">
			<div className="components-base-control__field">
				<legend className="components-base-control__label">{ label }</legend>
				{ help && <p className="components-base-control__help">{ help }</p> }
				{ options.map( ( option ) => (
					<div key={ option.value }>
						<CheckboxControl { ...omit( option, [ 'value' ] ) } checked={ isChecked( option ) } onChange={ update( option ) } />
					</div>
				) ) }
			</div>
		</fieldset>
	);
}
