/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useInstanceId } from '@wordpress/compose';
import { Icon, check, reset } from '@wordpress/icons';
import { BaseControl, VisuallyHidden } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './style.scss';

export default function CheckboxControl( {
	id,
	label,
	hideLabelFromVision,
	className,
	heading,
	checked,
	help,
	onChange,
	indeterminate,
	...props
} ) {
	const instanceId = useInstanceId( CheckboxControl );
	id = id || `itsec-inspector-checkbox-control-${ instanceId }`;
	const onChangeValue = ( event ) => onChange( event.target.checked );

	return (
		<BaseControl
			label={ heading }
			id={ id }
			help={ help }
			className={ className }
		>
			<span className="components-checkbox-control__input-container">
				<input
					id={ id }
					className={ classnames(
						'components-checkbox-control__input',
						{
							'components-checkbox-control__input--indeterminate': indeterminate,
						}
					) }
					type="checkbox"
					value="1"
					onChange={ onChangeValue }
					checked={ checked }
					aria-describedby={ !! help ? id + '__help' : undefined }
					{ ...props }
					ref={ ( ref ) =>
						ref && ( ref.indeterminate = indeterminate )
					}
				/>
				{ checked && (
					<Icon
						icon={ check }
						className="components-checkbox-control__checked"
						role="presentation"
					/>
				) }
				{ indeterminate && (
					<Icon
						icon={ reset }
						className="components-checkbox-control__checked"
						role="presentation"
					/>
				) }
			</span>
			{ label &&
				( hideLabelFromVision ? (
					<VisuallyHidden as="label" htmlFor={ id }>
						{ label }
					</VisuallyHidden>
				) : (
					<label
						className="components-checkbox-control__label"
						htmlFor={ id }
					>
						{ label }
					</label>
				) ) }
		</BaseControl>
	);
}
