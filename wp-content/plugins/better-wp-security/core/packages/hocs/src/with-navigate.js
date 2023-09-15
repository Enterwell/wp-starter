/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';

function isModifiedEvent( event ) {
	return !! (
		event.metaKey ||
		event.altKey ||
		event.ctrlKey ||
		event.shiftKey
	);
}

export default createHigherOrderComponent( ( WrappedComponent ) => {
	return function( { navigate, ...props } ) {
		const onClick = ( event ) => {
			try {
				if ( props.onClick ) {
					props.onClick( event );
				}
			} catch ( ex ) {
				event.preventDefault();
				throw ex;
			}

			if (
				! event.defaultPrevented && // onClick prevented default
				event.button === 0 && // ignore everything but left clicks
				( ! props.target || props.target === '_self' ) && // let browser handle "target=_blank" etc.
				! isModifiedEvent( event ) // ignore clicks with modifier keys
			) {
				event.preventDefault();
				navigate();
			}
		};

		return <WrappedComponent { ...props } onClick={ onClick } />;
	};
}, 'withNavigate' );
