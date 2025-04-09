/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';

export default function useLocalStorage( key, initialValue ) {
	const [ storedValue, setStoredValue ] = useState( () => {
		try {
			const item = window.localStorage.getItem( key );

			return item ? JSON.parse( item ) : initialValue;
		} catch ( error ) {
			// eslint-disable-next-line no-console
			console.error( error );

			return initialValue;
		}
	} );

	// Return a wrapped version of useState's setter function that
	// persists the new value to localStorage.
	const setValue = ( value ) => {
		try {
			const valueToStore =
				value instanceof Function ? value( storedValue ) : value;

			setStoredValue( valueToStore );
			window.localStorage.setItem( key, JSON.stringify( valueToStore ) );
		} catch ( error ) {
			// eslint-disable-next-line no-console
			console.error( error );
		}
	};

	return [ storedValue, setValue ];
}
