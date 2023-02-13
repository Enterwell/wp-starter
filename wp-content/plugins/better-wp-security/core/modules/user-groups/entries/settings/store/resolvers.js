/**
 * Internal dependencies
 */
import { dispatch } from './controls';

export function* getAvailableGroups() {
	const groups = yield dispatch( 'ithemes-security/user-groups', 'query', 'available', { _embed: 1 } );

	if ( groups.length > 0 ) {
		yield dispatch( 'ithemes-security/user-groups-editor', 'selectGroup', [ groups[ 0 ].id ] );
	}

	return groups;
}
