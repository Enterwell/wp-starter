/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';

/**
 * Internal dependencies
 */
import ControlledTabPanel from './controlled';
import ControlledMultiTabPanel from './multi';

export { ControlledTabPanel, ControlledMultiTabPanel };

export default class UncontrolledTabPanel extends Component {
	constructor() {
		super( ...arguments );

		this.state = {
			selected: this.props.initialTab || '',
		};
	}

	onSelect = ( selected ) => {
		this.setState( { selected } );
	};

	render() {
		return ( <ControlledTabPanel { ...this.props } selected={ this.state.selected } onSelect={ this.onSelect } /> );
	}
}
