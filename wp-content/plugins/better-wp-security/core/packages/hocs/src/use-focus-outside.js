/**
 * External dependencies
 */
import { includes } from 'lodash';

/**
 * WordPress dependencies
 */
import { useCallback, useEffect, useRef } from '@wordpress/element';

/**
 * Input types which are classified as button types, for use in considering
 * whether element is a (focus-normalized) button.
 *
 * @type {string[]}
 */
const INPUT_BUTTON_TYPES = [ 'button', 'submit' ];

/**
 * Returns true if the given element is a button element subject to focus
 * normalization, or false otherwise.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/button#Clicking_and_focus
 *
 * @param {EventTarget} eventTarget The target from a mouse or touch event.
 *
 * @return {boolean} Whether element is a button.
 */
function isFocusNormalizedButton( eventTarget ) {
	if ( ! ( eventTarget instanceof window.HTMLElement ) ) {
		return false;
	}
	switch ( eventTarget.nodeName ) {
		case 'A':
		case 'BUTTON':
			return true;

		case 'INPUT':
			return includes(
				INPUT_BUTTON_TYPES,
				/** @type {HTMLInputElement} */ ( eventTarget ).type
			);
	}

	return false;
}
/**
 * A react hook that can be used to check whether focus has moved outside the
 * element the event handlers are bound to.
 *
 * @param {Function} onFocusOutside A callback triggered when focus moves outside
 *                                  the element the event handlers are bound to.
 *
 * @return {Object} An object containing event handlers. Bind the event handlers
 *                  to a wrapping element element to capture when focus moves
 *                  outside that element.
 */
export default function useFocusOutside( onFocusOutside ) {
	const currentOnFocusOutside = useRef( onFocusOutside );
	useEffect( () => {
		currentOnFocusOutside.current = onFocusOutside;
	}, [ onFocusOutside ] );

	const preventBlurCheck = useRef( false );

	const blurCheckTimeoutId = useRef();

	/**
	 * Cancel a blur check timeout.
	 */
	const cancelBlurCheck = useCallback( () => {
		clearTimeout( blurCheckTimeoutId.current );
	}, [] );

	// Cancel blur checks on unmount.
	useEffect( () => {
		return () => cancelBlurCheck();
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] );

	// Cancel a blur check if the callback or ref is no longer provided.
	useEffect( () => {
		if ( ! onFocusOutside ) {
			cancelBlurCheck();
		}
	}, [ onFocusOutside, cancelBlurCheck ] );

	/**
	 * Handles a mousedown or mouseup event to respectively assign and
	 * unassign a flag for preventing blur check on button elements. Some
	 * browsers, namely Firefox and Safari, do not emit a focus event on
	 * button elements when clicked, while others do. The logic here
	 * intends to normalize this as treating click on buttons as focus.
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/button#Clicking_and_focus
	 *
	 * @param {Event} event Event for mousedown or mouseup.
	 */
	const normalizeButtonFocus = useCallback( ( event ) => {
		const { type, target } = event;
		const isInteractionEnd = includes( [ 'mouseup', 'touchend' ], type );

		if ( isInteractionEnd ) {
			preventBlurCheck.current = false;
		} else if ( isFocusNormalizedButton( target ) ) {
			preventBlurCheck.current = true;
		}
	}, [] );

	/**
	 * A callback triggered when a blur event occurs on the element the handler
	 * is bound to.
	 *
	 * Calls the `onFocusOutside` callback in an immediate timeout if focus has
	 * move outside the bound element and is still within the document.
	 *
	 * @param {Event} event Blur event.
	 */
	const queueBlurCheck = useCallback( ( event ) => {
		// React does not allow using an event reference asynchronously
		// due to recycling behavior, except when explicitly persisted.
		event.persist();

		// Skip blur check if clicking button. See `normalizeButtonFocus`.
		if ( preventBlurCheck.current ) {
			return;
		}

		blurCheckTimeoutId.current = setTimeout( () => {
			// If document is not focused then focus should remain
			// inside the wrapped component and therefore we cancel
			// this blur event thereby leaving focus in place.
			// https://developer.mozilla.org/en-US/docs/Web/API/Document/hasFocus.
			if ( ! document.hasFocus() ) {
				event.preventDefault();
				return;
			}

			if ( 'function' === typeof currentOnFocusOutside.current ) {
				currentOnFocusOutside.current( event );
			}
		}, 0 );
	}, [] );

	return {
		onFocus: cancelBlurCheck,
		onMouseDown: normalizeButtonFocus,
		onMouseUp: normalizeButtonFocus,
		onTouchStart: normalizeButtonFocus,
		onTouchEnd: normalizeButtonFocus,
		onBlur: queueBlurCheck,
	};
}
