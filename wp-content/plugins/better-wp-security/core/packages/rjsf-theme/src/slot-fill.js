/**
 * WordPress dependencies
 */
import { Slot, Fill } from '@wordpress/components';

export function RjsfFieldFill( { name, ...props } ) {
	return <Fill name={ `RjsfField${ name }` } { ...props } />;
}

export function RjsfFieldSlot( { name, ...props } ) {
	return <Slot name={ `RjsfField${ name }` } { ...props } />;
}
