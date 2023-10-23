/**
 * WordPress dependencies
 */
import { controls } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { store } from '@ithemes/security.user-groups.api';

export function* getAvailableGroups() {
	yield controls.dispatch( store, 'query', 'available', {
		_embed: 1,
	} );
}
