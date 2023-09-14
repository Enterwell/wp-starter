/**
 * External dependencies
 */
import { isEmpty, groupBy, map } from 'lodash';

/**
 * WordPress dependencies
 */
import { useInstanceId } from '@wordpress/compose';
import { BaseControl } from '@wordpress/components';

export default function SelectControl( {
	help,
	label,
	multiple = false,
	onChange,
	options = [],
	className,
	hideLabelFromVision,
	...props
} ) {
	const instanceId = useInstanceId( SelectControl );
	const id = `inspector-select-control-${ instanceId }`;
	const onChangeValue = ( event ) => {
		if ( multiple ) {
			const selectedOptions = [ ...event.target.options ].filter(
				( { selected } ) => selected
			);
			const newValues = selectedOptions.map( ( { value } ) => value );
			onChange( newValues );
			return;
		}
		onChange( event.target.value );
	};

	const grouped = groupBy( options, 'optgroup' );

	// Disable reason: A select with an onchange throws a warning

	/* eslint-disable jsx-a11y/no-onchange */
	return (
		! isEmpty( options ) && (
			<BaseControl
				label={ label }
				hideLabelFromVision={ hideLabelFromVision }
				id={ id }
				help={ help }
				className={ className }
			>
				<select
					id={ id }
					className="components-select-control__input"
					onChange={ onChangeValue }
					aria-describedby={ !! help ? `${ id }__help` : undefined }
					multiple={ multiple }
					{ ...props }
				>
					{ map( grouped, ( perGroup, optgroup ) => {
						const optionList = perGroup.map( ( option, index ) => (
							<option
								key={ `${ option.label }-${ option.value }-${ index }` }
								value={ option.value }
								disabled={ option.disabled }
							>
								{ option.label }
							</option>
						) );

						return optgroup === 'undefined' ? (
							optionList
						) : (
							<optgroup label={ optgroup } key={ optgroup }>
								{ optionList }
							</optgroup>
						);
					} ) }
				</select>
			</BaseControl>
		)
	);
	/* eslint-enable jsx-a11y/no-onchange */
}
