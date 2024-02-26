/**
 * Internal dependencies
 */
import { dispatch, apiFetch } from '../controls';
import { STORE_NAME, path } from './constant';
import { receiveLog } from './actions';

export function* getLogs() {
	yield dispatch( STORE_NAME, 'query', 'main', {
		per_page: 100,
	} );
}

export function* getLogById( id ) {
	const log = yield apiFetch( {
		path: `${ path }/${ id }`,
	} );
	yield receiveLog( id );

	return log;
}
