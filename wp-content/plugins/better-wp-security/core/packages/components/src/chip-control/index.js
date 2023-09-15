/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useInstanceId } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import './style.scss';

export default function ChipControl( {
	id,
	checked,
	onChange,
	label,
	className,
	...rest
} ) {
	id = useInstanceId( ChipControl, 'itsec-chip-control' ) || id;

	return (
		<div className={ classnames( 'itsec-chip-control', className ) }>
			<input
				type="checkbox"
				checked={ checked }
				onChange={ ( e ) => onChange( e.target.checked ) }
				id={ id }
				{ ...rest }
			/>
			<label htmlFor={ id }>{ label }</label>
		</div>
	);
}
