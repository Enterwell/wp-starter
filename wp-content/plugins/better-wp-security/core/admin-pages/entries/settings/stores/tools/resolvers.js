import { controls } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { fetchTools } from './actions';
import { TOOLS_STORE_NAME } from '../';

export function* getTools() {
	yield fetchTools();
}

export function* getResolvedTools() {
	yield controls.resolveSelect( TOOLS_STORE_NAME, 'getTools' );
}

export const getTool = {
	*fulfill() {
		yield controls.resolveSelect( TOOLS_STORE_NAME, 'getTools' );
	},
	isFulfilled( state, tool ) {
		return !! state.bySlug[ tool ];
	},
};
