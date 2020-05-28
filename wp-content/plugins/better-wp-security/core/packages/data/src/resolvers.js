/**
 * Internal dependencies
 */
import { apiFetch } from './controls';
import { receiveIndex, receiveUser } from './actions';

export function* getIndex() {
	const index = yield apiFetch( { path: '/ithemes-security/v1?context=help' } );
	yield receiveIndex( index );
}

export const getUser = {
	*fulfill( userId ) {
		const user = yield apiFetch( {
			path: `/wp/v2/users/${ userId }`,
		} );

		yield receiveUser( user );
	},
	isFulfilled( state, userId ) {
		return !! state.users.byId[ userId ];
	},
};

