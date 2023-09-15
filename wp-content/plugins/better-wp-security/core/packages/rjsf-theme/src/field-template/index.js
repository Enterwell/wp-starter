/**
 * External dependencies
 */
import { utils } from '@rjsf/core';

/**
 * WordPress dependencies
 */
import { Button, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { RjsfFieldSlot } from '../slot-fill';
import './style.scss';

const { ADDITIONAL_PROPERTY_FLAG, getUiOptions } = utils;

export default function FieldTemplate( props ) {
	const {
		children,
		errors,
		hidden,
		schema,
		uiSchema,
		formContext,
		onChange,
	} = props;

	if ( hidden ) {
		/*return <div className="hidden">{ children }</div>;*/
		return null;
	}

	const { resettable } = getUiOptions( uiSchema );

	return (
		<WrapIfAdditional { ...props }>
			{ children }
			<RjsfFieldSlot name={ props.id } fillProps={ props } />
			{ resettable && (
				<Button
					className="itsec-rjsf-reset-field"
					variant="secondary"
					onClick={ () => onChange( schema.default ) }
				>
					{ __( 'Restore Default', 'better-wp-security' ) }
				</Button>
			) }
			{ formContext?.disableInlineErrors !== true && errors }
		</WrapIfAdditional>
	);
}

function WrapIfAdditional( props ) {
	const {
		id,
		classNames,
		disabled,
		label,
		onKeyChange,
		onDropPropertyClick,
		readonly,
		required,
		schema,
		uiSchema,
	} = props;
	const keyLabel = `${ label } Key`; // i18n ?
	const additional = schema.hasOwnProperty( ADDITIONAL_PROPERTY_FLAG );
	const { removable } = getUiOptions( uiSchema );

	if ( ! additional || removable === false ) {
		return <div className={ classNames }>{ props.children }</div>;
	}

	return (
		<div className={ classNames }>
			<div className="row">
				<div className="col-xs-5 form-additional">
					<TextControl
						label={ keyLabel }
						required={ required }
						id={ `${ id }-key` }
						onBlur={ ( e ) => onKeyChange( e.target.value ) }
					/>
				</div>
				<div className="form-additional form-group col-xs-5">
					{ props.children }
				</div>
				<div className="col-xs-2">
					<Button
						icon="no-alt"
						isDestructive
						disabled={ disabled || readonly }
						onClick={ onDropPropertyClick( label ) }
					/>
				</div>
			</div>
		</div>
	);
}
