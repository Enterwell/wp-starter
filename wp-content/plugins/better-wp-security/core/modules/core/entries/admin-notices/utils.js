/**
 * Check if the given element belongs to the notices panel even if it is outside of the DOM tree.
 *
 * @param {HTMLElement} element
 * @return {boolean} If the element is protected.
 */
export function doesElementBelongToPanel( element ) {
	let node = element.parentNode;

	while ( node !== null ) {
		if ( node.classList && node.classList.contains( 'itsec-admin-notice-list-actions__more-menu-items' ) ) {
			return true;
		}

		node = node.parentNode;
	}

	return false;
}
