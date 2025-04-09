/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';

const noop = () => {};

export default class extends Component {
	constructor( props ) {
		super( props );

		this.state = {
			isHovering: false,
		};

		this.onMouseEnter = this.onMouseEnter.bind( this );
		this.onMouseLeave = this.onMouseLeave.bind( this );
		this.onMouseOver = this.onMouseOver.bind( this );
		this.onMouseOut = this.onMouseOut.bind( this );
		this.setIsHovering = this.setIsHovering.bind( this );
		this.unsetIsHovering = this.unsetIsHovering.bind( this );
		this.componentWillUnmount = this.componentWillUnmount.bind( this );

		this.timerIds = [];
	}

	static displayName = 'HoverDetector';

	static defaultProps = {
		hoverDelayInMs: 0,
		hoverOffDelayInMs: 0,
		onHoverChanged: noop,
		onMouseEnter: ( { setIsHovering } ) => setIsHovering(),
		onMouseLeave: ( { unsetIsHovering } ) => unsetIsHovering(),
		onMouseOver: noop,
		onMouseOut: noop,
		shouldDecorateChildren: true,
	};

	onMouseEnter( e ) {
		this.props.onMouseEnter( {
			e,
			setIsHovering: this.setIsHovering,
			unsetIsHovering: this.unsetIsHovering,
		} );
	}

	onMouseLeave( e ) {
		this.props.onMouseLeave( {
			e,
			setIsHovering: this.setIsHovering,
			unsetIsHovering: this.unsetIsHovering,
		} );
	}

	onMouseOver( e ) {
		this.props.onMouseOver( {
			e,
			setIsHovering: this.setIsHovering,
			unsetIsHovering: this.unsetIsHovering,
		} );
	}

	onMouseOut( e ) {
		this.props.onMouseOut( {
			e,
			setIsHovering: this.setIsHovering,
			unsetIsHovering: this.unsetIsHovering,
		} );
	}

	componentWillUnmount() {
		this.clearTimers();
	}

	setIsHovering() {
		this.clearTimers();

		const hoverScheduleId = setTimeout( () => {
			const newState = { isHovering: true };
			this.setState( newState, () => {
				this.props.onHoverChanged( newState );
			} );
		}, this.props.hoverDelayInMs );

		this.timerIds.push( hoverScheduleId );
	}

	unsetIsHovering() {
		this.clearTimers();

		const hoverOffScheduleId = setTimeout( () => {
			const newState = { isHovering: false };
			this.setState( newState, () => {
				this.props.onHoverChanged( newState );
			} );
		}, this.props.hoverOffDelayInMs );

		this.timerIds.push( hoverOffScheduleId );
	}

	clearTimers() {
		const ids = this.timerIds;
		while ( ids.length ) {
			clearTimeout( ids.pop() );
		}
	}

	render() {
		const { children, className } = this.props;

		return (
			// eslint-disable-next-line jsx-a11y/mouse-events-have-key-events
			<div
				{ ...{
					className,
					onMouseEnter: this.onMouseEnter,
					onMouseLeave: this.onMouseLeave,
					onMouseOver: this.onMouseOver,
					onMouseOut: this.onMouseOut,
				} }
			>
				{ children }
			</div>
		);
	}
}
