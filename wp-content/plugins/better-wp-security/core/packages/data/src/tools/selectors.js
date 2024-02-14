/**
 * External dependencies
 */
import createSelector from 'rememo';
import memize from 'memize';

/**
 * WordPress dependencies
 */
import { createRegistrySelector } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { MODULES_STORE_NAME } from '../index';
import { STORE_NAME } from './constant';

const _combineTools = memize(
	( fromConfig, fromApi ) =>
		fromConfig.map( ( config ) => fromApi[ config.slug ] || config ),
	{ maxSize: 1 }
);

export const getResolvedTools = createRegistrySelector(
	( select ) => ( state ) =>
		_combineTools(
			select( STORE_NAME ).getToolsConfig(),
			state.bySlug
		)
);

export const getTools = createSelector(
	( state ) => state.slugs.map( ( slug ) => state.bySlug[ slug ] ),
	( state ) => [ state.bySlug, state.slugs ]
);

const _transformTools = memize(
	( modules ) => {
		return modules.reduce( ( acc, module ) => {
			for ( const [ slug, config ] of Object.entries( module.tools ) ) {
				acc.push( {
					slug,
					module: module.id,
					toggleable: false,
					schedule: '',
					form: null,
					...config,
				} );
			}

			return acc;
		}, [] );
	},
	{ maxSize: 1 }
);

export const getToolsConfig = createRegistrySelector( ( select ) => () =>
	_transformTools( select( MODULES_STORE_NAME ).getModules() )
);

export const getTool = createRegistrySelector( ( select ) => ( state, tool ) =>
	state.bySlug[ tool ] ||
	select( STORE_NAME )
		.getToolsConfig()
		.find( ( maybe ) => tool === maybe.slug )
);

export function getRunning( state ) {
	return state.running;
}

export function isRunning( state, tool ) {
	return state.running.includes( tool );
}

export function getLastResult( state, tool ) {
	return state.lastResult[ tool ];
}

export function isUpdating( state, tool ) {
	return state.updating.includes( tool );
}

export function getLastError( state, tool ) {
	return state.lastError[ tool ];
}
