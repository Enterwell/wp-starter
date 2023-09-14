/**
 * Internal dependencies
 */
import { dispatch } from './controls';

export function* getAvailableGroups() {
	yield dispatch( 'ithemes-security/user-groups', 'query', 'available', {
		_embed: 1,
	} );
}
