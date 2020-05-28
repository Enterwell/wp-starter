/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { Component } from '@wordpress/element';

export default createHigherOrderComponent( ( WrappedComponent ) => {
	return class WithPressedModifierKeys extends Component {
		state = {
			pressed: {
				shift: false,
				ctrl: false,
				meta: false,
				alt: false,
			},
		};

		mounted = false;

		constructor() {
			super( ...arguments );

			this.listener = this.listener.bind( this );
			this.onBlur = this.onBlur.bind( this );
		}

		componentDidMount() {
			this.mounted = true;
			window.addEventListener( 'keydown', this.listener );
			window.addEventListener( 'keyup', this.listener );
			window.addEventListener( 'click', this.listener );
			window.addEventListener( 'blur', this.onBlur );
		}

		componentWillUnmount() {
			this.mounted = false;
			window.removeEventListener( 'keydown', this.listener );
			window.removeEventListener( 'keyup', this.listener );
			window.removeEventListener( 'click', this.listener );
			window.removeEventListener( 'blur', this.onBlur );
		}

		/**
		 * Fires whenever a key is pressed down.
		 * @param {KeyboardEvent} e
		 */
		listener( e ) {
			if ( this.mounted ) {
				this.setState( {
					pressed: {
						shift: e.shiftKey,
						ctrl: e.ctrlKey,
						meta: e.metaKey,
						alt: e.altKey,
					},
				} );
			}
		}

		/**
		 * When the window blurs, remove all pressed modifier keys.
		 */
		onBlur() {
			this.setState( {
				pressed: {
					shift: false,
					ctrl: false,
					meta: false,
					alt: false,
				},
			} );
		}

		render() {
			return <WrappedComponent pressedModifierKeys={ this.state.pressed } { ...this.props } />;
		}
	};
}, 'withPressedModifierKeys' );
