/* global itsecWebpackPublicPath */
/**
 * External dependencies
 */
import { useRive } from '@rive-app/react-canvas';

/**
 * WordPress dependencies
 */
import { useEffect, createContext, useState, useCallback, useContext } from '@wordpress/element';

/**
 * @typedef {import('@rive-app/react-canvas').UseRiveParameters} UseRiveParameters
 * @typedef {import('@rive-app/react-canvas').UseRiveOptions} UseRiveOptions
 * @typedef {import('@rive-app/react-canvas').RiveState} RiveState
 * @typedef {import('react')} React
 */

/**
 * @type {{preloaded: Record<string, ArrayBuffer|null|undefined>}}
 */
const defaultContext = {
	preloaded: {},
};

const Context = createContext( defaultContext );

/**
 * Provides Rive graphics to downstream components.
 *
 * @param {Object}          props
 * @param {string[]}        props.preload  Names of graphics to preload.
 * @param {React.ReactNode} props.children
 */
export function RiveGraphicProvider( { preload, children } ) {
	const [ context, setContext ] = useState( {
		preloaded: {},
	} );

	const updateState = useCallback( ( name, buffer ) => {
		setContext( ( current ) => ( {
			...current,
			preloaded: {
				...current.preloaded,
				[ name ]: buffer,
			},
		} ) );
	}, [ setContext ] );

	useEffect( () => {
		for ( const name of preload ) {
			if ( context.preloaded.hasOwnProperty( name ) ) {
				continue;
			}

			updateState( name, undefined );
			fetch( getSource( name ) )
				.then( ( response ) => response.arrayBuffer() )
				.then( ( buffer ) => updateState( name, buffer ) )
				.catch( ( error ) => {
					// eslint-disable-next-line no-console
					console.error( `[Solid Security] Could not load rive graphic '${ name }': ${ error }` );
					updateState( name, null );
				} );
		}
	}, [ preload, context.preloaded, updateState ] );

	return (
		<Context.Provider value={ context }>{ children }</Context.Provider>
	);
}

/**
 * Loads a Rive graphic.
 *
 * If the file has been preloaded by useRiveGraphicPreloader(), it will be available
 * immediately. Otherwise, Rive will fetch it.
 *
 * @param {string}            name      The graphic's name, without its extension.
 * @param {UseRiveParameters} [params]  Additional parameters.
 * @param {UseRiveOptions}    [options] Additional options.
 * @return {RiveState} The loaded Rive graphic.
 */
export function usePreloadedRiveGraphic( name, params, options ) {
	const { preloaded } = useContext( Context );

	if ( preloaded[ name ] ) {
		params.buffer = preloaded[ name ];
	} else {
		params.src = getSource( name );
	}

	return useRive( params, options );
}

function getSource( name ) {
	return `${ itsecWebpackPublicPath }../core/img/rive/${ name }.riv`;
}
