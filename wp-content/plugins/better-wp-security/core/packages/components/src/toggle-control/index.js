/**
 * External dependencies
 */
import { isFunction } from 'lodash';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import { withInstanceId } from '@wordpress/compose';
import { FormToggle, BaseControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './style.scss';

/**
 * This is a copy of core's toggle control but passes thru additional props to the FormToggle.
 */
class ToggleControl extends Component {
	constructor() {
		super( ...arguments );

		this.onChange = this.onChange.bind( this );
	}

	onChange( event ) {
		if ( this.props.onChange ) {
			this.props.onChange( event.target.checked );
		}
	}

	render() {
		const { label, checked, help, instanceId, ...props } = this.props;
		const id = `inspector-toggle-control-${ instanceId }`;

		let describedBy, helpLabel;
		if ( help ) {
			describedBy = id + '__help';
			helpLabel = isFunction( help ) ? help( checked ) : help;
		}

		return (
			<BaseControl
				id={ id }
				help={ helpLabel }
				className="components-toggle-control"
			>
				<FormToggle
					{ ...props }
					id={ id }
					checked={ checked }
					onChange={ this.onChange }
					aria-describedby={ describedBy }
				/>
				<label
					htmlFor={ id }
					className="components-toggle-control__label"
				>
					{ label }
				</label>
			</BaseControl>
		);
	}
}

export default withInstanceId( ToggleControl );
