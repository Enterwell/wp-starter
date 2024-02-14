/**
 * Internal dependencies
 */
import { dispatch, apiFetch } from '../controls';
import { STORE_NAME, path } from './constant';
import { receiveVulnerability } from './actions';

export function* getVulnerabilities() {
	yield dispatch( STORE_NAME, 'query', 'main', {
		per_page: 100, resolution: [ 'unresolved', 'patched', 'deactivated' ],
	} );
}

export function* getVulnerabilityById( id ) {
	const vulnerability = yield apiFetch( {
		path: `${ path }/${ id }`,
	} );
	yield receiveVulnerability( vulnerability );

	return vulnerability;
}
