/**
 * Internal dependencies
 */
import { dispatch, apiFetch } from '../controls';
import { STORE_NAME, path } from './constant';
import { receiveUser } from './actions';

export function* getUsers() {
	yield dispatch( STORE_NAME, 'query', 'main', {
		per_page: 20,
		context: 'edit',
		roles: [ 'administrator' ],
	} );
}

export function* getUserById( id ) {
	const user = yield apiFetch( {
		path: `${ path }/${ id }?context=edit`,
	} );
	yield receiveUser( user );

	return user;
}
