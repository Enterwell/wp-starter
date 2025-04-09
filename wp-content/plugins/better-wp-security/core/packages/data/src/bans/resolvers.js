/**
 * Internal dependencies
 */
import { dispatch } from '../controls';

export function* getBans() {
	yield dispatch( 'ithemes-security/bans', 'query', 'main', {
		per_page: 100,
	} );
}
