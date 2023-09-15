/**
 * WordPress dependencies
 */
import { useRef, useEffect } from '@wordpress/element';

// https://usehooks.com/useEventListener/

/**
 * Uses an event listener callback.
 *
 * @param {string}         type     The dom event to listen for.
 * @param {Function}       listener The handler function to call.
 * @param {Element|Window} element  The element to listen for effects on.
 */
export default function useEventListener( type, listener, element = window ) {
	const savedHandler = useRef();

	// Update ref.current value if handler changes.
	// This allows our effect below to always get latest handler ...
	// ... without us needing to pass it in effect deps array ...
	// ... and potentially cause effect to re-run every render.
	useEffect( () => {
		savedHandler.current = listener;
	}, [ listener ] );

	useEffect( () => {
		if ( ! element || ! element.addEventListener ) {
			return;
		}

		const eventListener = ( event ) => savedHandler.current( event );
		element.addEventListener( type, eventListener );

		return () => element.removeEventListener( type, eventListener );
	}, [ type, element ] );
}
