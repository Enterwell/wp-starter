/**
 * External dependencies
 */
import { omit } from 'lodash';

/**
 * Internal dependencies
 */
import { REGISTER_PROVIDER } from './actions';

const INITIAL_STATE = {
	providers: {},
};

export default function reducer( state = INITIAL_STATE, action ) {
	switch ( action.type ) {
		case REGISTER_PROVIDER:
			return {
				...state,
				providers: {
					...state.providers,
					[ action.slug ]: omit( action, [ 'type' ] ),
				},
			};
		default:
			return state;
	}
}
