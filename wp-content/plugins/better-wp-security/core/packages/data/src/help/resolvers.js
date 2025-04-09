/**
 * Internal dependencies
 */
import { fetchHelp } from './actions';

export function* getHelp( topic ) {
	yield fetchHelp( topic );
}
