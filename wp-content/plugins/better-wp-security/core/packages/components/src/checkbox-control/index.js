/**
 * WordPress dependencies
 */
import { withInstanceId } from '@wordpress/compose';
import { BaseControl, Dashicon } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './style.scss';

function CheckboxControl( { label, className, heading, checked, help, instanceId, onChange, indeterminate, ...props } ) {
	const id = `inspector-checkbox-control-${ instanceId }`;
	const onChangeValue = ( event ) => onChange( event.target.checked );

	return (
		<BaseControl label={ heading } id={ id } help={ help } className={ className }>
			<span className="components-checkbox-control__input-container">
				<input
					id={ id }
					className="components-checkbox-control__input"
					type="checkbox"
					value="1"
					onChange={ onChangeValue }
					checked={ checked }
					aria-describedby={ !! help ? id + '__help' : undefined }
					ref={ ( ref ) => {
						if ( ref ) {
							ref.indeterminate = indeterminate;
						}
					} }
					{ ...props }
				/>
				{ checked ? <Dashicon icon="yes" className="components-checkbox-control__checked" role="presentation" /> : null }
				{ indeterminate ? <Dashicon icon="minus" className="components-checkbox-control__checked components-checkbox-control__checked--indeterminate" role="presentation" /> : null }
			</span>
			<label className="components-checkbox-control__label" htmlFor={ id }>
				{ label }
			</label>
		</BaseControl>
	);
}

export default withInstanceId( CheckboxControl );
