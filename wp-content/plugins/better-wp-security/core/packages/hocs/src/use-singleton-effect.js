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
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [] );
}
