/**
 * WordPress dependencies
 */
import { useState, useEffect, useCallback } from '@wordpress/element';

/**
 * A hook to manage making an async request.
 *
 * @typedef {('idle'|'pending'|'success'|'error')} Status
 *
 * @param {Function} asyncFunction The async function to execute.
 * @param {boolean}  immediate     Whether to immediate execute the async function.
 * @return {{error: Error, execute: Function, value: *, status: Status}} Hook info.
 */
export default function useAsync( asyncFunction, immediate = true ) {
	const [ status, setStatus ] = useState( 'idle' );
	const [ value, setValue ] = useState( null );
	const [ error, setError ] = useState( null );

	const execute = useCallback(
		( ...args ) => {
			setStatus( 'pending' );
			setError( null );

			return asyncFunction( ...args )
				.then( ( response ) => {
					setValue( response );

					setStatus( 'success' );
				} )
				.catch( ( _error ) => {
					setError( _error );
					setValue( null );

					setStatus( 'error' );
				} );
		},
		[ asyncFunction ]
	);

	useEffect( () => {
		if ( immediate ) {
			execute();
		}
	}, [ execute, immediate ] );

	return { execute, status, value, error };
}
