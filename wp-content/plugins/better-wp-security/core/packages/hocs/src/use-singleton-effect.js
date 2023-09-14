/**
 * WordPress dependencies
 */
import { useLayoutEffect } from '@wordpress/element';

const triggeredMap = new WeakMap();

export default function useSingletonEffect( singleton, effect ) {
	useLayoutEffect( () => {
		if ( ! triggeredMap.has( singleton ) ) {
			effect();
			triggeredMap.set( singleton, true );
		}
	}, [] );
}
