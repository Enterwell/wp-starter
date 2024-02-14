/**
 * External dependencies
 */
import { mapValues, toInteger } from 'lodash';

/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { useMemo } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useSingletonEffect } from '@ithemes/security-hocs';
import { LineGraph, PieChart } from './renderers';
import * as securitySummary from './security-summary';

export function useRegisterCards() {
	const { registerCard } = useDispatch( 'ithemes-security/dashboard' );

	useSingletonEffect( useRegisterCards, () =>
		[ securitySummary ].forEach( ( { slug, settings } ) =>
			registerCard( slug, settings )
		)
	);
}

export function useCardRenderer( config ) {
	const settings = useSelect(
		( select ) =>
			select( 'ithemes-security/dashboard' ).getRegisteredCard(
				config.slug
			),
		[ config.slug ]
	);

	if ( settings?.render ) {
		return settings.render;
	}

	switch ( config.type ) {
		case 'line':
			return LineGraph;
		case 'pie':
			return PieChart;
	}

	return null;
}

export function useCardElementQueries( config, style, gridWidth ) {
	const settings = useSelect(
		( select ) =>
			select( 'ithemes-security/dashboard' ).getRegisteredCard(
				config.slug
			),
		[ config.slug ]
	);
	const queries = settings?.elementQueries;

	return useMemo( () => {
		if ( ! queries ) {
			return {};
		}

		const size = {
			height: style.height
				? toInteger( style.height.replace( 'px', '' ) )
				: 0,
		};

		if ( style.width && style.width.endsWith( '%' ) ) {
			size.width =
				( toInteger( style.width.replace( '%', '' ) ) * gridWidth ) /
				100;
		} else {
			size.width = style.width
				? toInteger( style.width.replace( 'px', '' ) )
				: 0;
		}

		const props = {};

		for ( const query of queries ) {
			if ( ! size[ query.type ] ) {
				continue;
			}

			let pass = false;

			switch ( query.dir ) {
				case 'max':
					pass = size[ query.type ] <= query.px;
					break;
				case 'min':
					pass = size[ query.type ] >= query.px;
					break;
			}

			if ( ! pass ) {
				continue;
			}

			props[ `${ query.dir }-${ query.type }` ] =
				( props[ `${ query.dir }-${ query.type }` ] || '' ) +
				query.px +
				'px ';
		}

		return mapValues( props, ( str ) => str.trim() );
	}, [ queries, style, gridWidth ] );
}
