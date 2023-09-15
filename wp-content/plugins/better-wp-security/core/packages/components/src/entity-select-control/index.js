/**
 * External dependencies
 */
import { createGlobalState } from 'react-hooks-global-state';
import { mapValues, keyBy } from 'lodash';

/**
 * WordPress dependencies
 */
import { useState, useCallback } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { BaseControl } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { AsyncSelect, Markup } from '../';
import { useInstanceId } from '@wordpress/compose';

const { useGlobalState } = createGlobalState( { cache: {} } );

export default function EntitySelectControl( {
	id,
	value,
	disabled,
	readonly,
	onChange,
	label,
	description,
	isMultiple = false,
	path,
	query = {},
	labelAttr,
	idAttr = 'id',
	searchArg = 'search',
} ) {
	const [ cache, setCache ] = useGlobalState( 'cache' );
	const instanceId = useInstanceId(
		EntitySelectControl,
		'itsec-entity-select-control'
	);
	id = id || instanceId;

	const [ input, setInput ] = useState( '' );
	const loader = useLoader(
		path,
		query,
		labelAttr,
		idAttr,
		searchArg,
		cache,
		setCache
	);

	let asyncValue;

	if ( isMultiple ) {
		asyncValue = ( value || [] )
			.filter( ( item ) => item !== undefined )
			.map( ( itemId ) => ( {
				value: itemId,
				label: cache[ path ]?.[ itemId ] || itemId,
			} ) );
	} else if ( value ) {
		asyncValue = {
			value,
			label: cache[ path ]?.[ value ] || value,
		};
	}

	return (
		<BaseControl
			className="itsec-entity-select-control"
			label={ label }
			help={ <Markup noWrap content={ description } /> }
			id={ id }
		>
			<AsyncSelect
				aria-label={ label }
				aria-describedby={ description ? id + '__help' : undefined }
				classNamePrefix="itsec-entity-select-control-as"
				inputId={ id }
				isDisabled={ disabled || readonly }
				isMulti={ isMultiple }
				isClearable
				cacheOptions
				defaultOptions
				loadOptions={ loader }
				value={ asyncValue }
				onChange={ ( nextValue ) =>
					onChange(
						isMultiple
							? ( nextValue || [] ).map( ( item ) => item.value )
							: nextValue?.value
					)
				}
				inputValue={ input }
				onInputChange={ setInput }
			/>
		</BaseControl>
	);
}

function useLoader(
	path,
	query,
	labelAttr,
	idAttr,
	searchArg,
	cache,
	setCache
) {
	return useCallback(
		( search ) => {
			return apiFetch( {
				path: addQueryArgs( path, { ...query, [ searchArg ]: search } ),
			} )
				.then( ( response ) =>
					response.map( ( item ) => ( {
						value: item[ idAttr ],
						label: item[ labelAttr ],
					} ) )
				)
				.then( ( items ) => {
					setCache( {
						...cache,
						[ path ]: {
							...( cache[ path ] || {} ),
							...mapValues( keyBy( items, 'value' ), 'label' ),
						},
					} );

					return items;
				} );
		},
		[ path, query, labelAttr, idAttr, searchArg, cache ]
	);
}
