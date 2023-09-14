/**
 * WordPress dependencies
 */
import { useState, useRef, useEffect } from '@wordpress/element';

export default function useHover( { mouseOnly = true } = {} ) {
	const [ value, setValue ] = useState( false );
	const ref = useRef( null );

	const handlePointerOver = ( e ) => {
		if ( mouseOnly && e.pointerType !== 'mouse' ) {
			return;
		}

		setValue( true );
	};
	const handlePointerOut = ( e ) => {
		if ( mouseOnly && e.pointerType !== 'mouse' ) {
			return;
		}

		setValue( false );
	};

	useEffect(
		() => {
			const node = ref.current;
			if ( node ) {
				node.addEventListener( 'pointerover', handlePointerOver );
				node.addEventListener( 'pointerout', handlePointerOut );

				return () => {
					node.removeEventListener(
						'pointerover',
						handlePointerOver
					);
					node.removeEventListener( 'pointerout', handlePointerOut );
				};
			}
		},
		[ ref.current ] // Recall only if ref changes
	);
	return [ ref, value ];
}
