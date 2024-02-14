/**
 * Internal dependencies
 */
import { dispatch } from '../controls';
import { STORE_NAME } from './constant';

export function* getScans() {
	yield dispatch( STORE_NAME, 'query', 'main', {
		per_page: 100,
	} );
}
