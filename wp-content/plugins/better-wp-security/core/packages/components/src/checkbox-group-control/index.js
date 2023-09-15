/**
 * External dependencies
 */
import { omit, isArray } from 'lodash';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { CheckboxControl } from '@wordpress/components';

export default function CheckboxGroupControl( {
	value,
	onChange,
	options,
	label,
	help,
	disabled,
	readOnly,
	className,
} ) {
	let isChecked, update;

	if ( isArray( value ) ) {
		isChecked = ( option ) => value.includes( option.value );
		update = ( option ) => ( checked ) =>
			onChange(
				checked
					? [ ...value, option.value ]
					: value.filter(
						( maybeValue ) => maybeValue !== option.value
					)
			);
	} else {
		isChecked = ( option ) => value[ option.value ] || false;
		update = ( option ) => ( checked ) =>
			onChange( { ...value, [ option.value ]: checked } );
	}

	return (
		<fieldset
			className={ classnames( 'components-base-control', className ) }
		>
			<div className="components-base-control__field">
				<legend className="components-base-control__label">
					{ label }
				</legend>
				{ help && (
					<p className="components-base-control__help">{ help }</p>
				) }
				<div className="itsec-components-checkbox-group-control__options">
					{ options.map( ( option ) => (
						<CheckboxControl
							{ ...omit( option, [
								'value',
								'disabled',
								'readOnly',
							] ) }
							key={ option.value }
							checked={ isChecked( option ) }
							onChange={ update( option ) }
							disabled={ disabled || option.disabled }
							readOnly={ readOnly || option.readOnly }
							className={
								isChecked( option ) &&
								'itsec-components-checkbox-group-control__option--is-checked'
							}
						/>
					) ) }
				</div>
			</div>
		</fieldset>
	);
}
