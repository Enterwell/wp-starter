/**
 * WordPress dependencies
 */
import { useState, useCallback } from '@wordpress/element';

export default function useSet( initialValue = [] ) {
	const [ items, setItems ] = useState( initialValue );

	return [
		items,
		// Add
		useCallback( ( item ) =>
			setItems( ( latestItems ) => [ ...latestItems, item ] ),
		[]
		),
		// Remove
		useCallback( ( item ) =>
			setItems( ( latestItems ) =>
				latestItems.filter( ( maybeItem ) => maybeItem !== item )
			),
		[]
		),
		setItems,
	];
}
