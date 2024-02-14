import { controls } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { fetchTools } from './actions';
import { STORE_NAME } from './constant';

export function* getTools() {
	yield fetchTools();
}

export function* getResolvedTools() {
	yield controls.resolveSelect( STORE_NAME, 'getTools' );
}

export const getTool = {
	*fulfill() {
		yield controls.resolveSelect( STORE_NAME, 'getTools' );
	},
	isFulfilled( state, tool ) {
		return !! state.bySlug[ tool ];
	},
};
