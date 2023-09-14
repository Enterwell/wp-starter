/**
 * External dependencies
 */
import { omitBy } from 'lodash';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

/**
 * iThemes dependencies
 */
import { SearchControl } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { SelectControl } from '@ithemes/security-components';

function useActorsSelect( emptyLabel = '' ) {
	const { types, byType } = useSelect( ( select ) => {
		const selectTypes =
			select( 'ithemes-security/core' ).getActorTypes() || [];
		const selectByType = {};

		for ( const type of selectTypes ) {
			selectByType[ type.slug ] = select(
				'ithemes-security/core'
			).getActors( type.slug );
		}

		return { types: selectTypes, byType: selectByType };
	}, [] );

	const options = [];
	options.push( {
		label: emptyLabel,
		value: '',
	} );

	for ( const type of types ) {
		options.push( {
			label: sprintf(
				/* translators: 1. Actor type label */
				__( 'Any %s', 'better-wp-security' ),
				type.label
			),
			value: type.slug,
			optgroup: type.label,
		} );

		for ( const actor of byType[ type.slug ] || [] ) {
			options.push( {
				label: actor.label,
				value: type.slug + ':' + actor.id,
				optgroup: type.label,
			} );
		}
	}

	return options;
}

export default function Search( { query, isQuerying } ) {
	const actors = useActorsSelect( __( 'All', 'better-wp-security' ) );
	const [ search, setSearch ] = useState( {
		search: '',
		actor_id: '',
		actor_type: '',
	} );
	const onSearch = ( change ) => {
		const newSearch = { ...search, ...change };
		setSearch( newSearch );
		query( 'main', {
			...omitBy( newSearch, ( value ) => value === '' ),
			per_page: 100,
		} );
	};

	return (
		<section className="itsec-card-banned-users__search">
			<SelectControl
				options={ actors }
				hideLabelFromVision
				label={ __( 'Ban Reason', 'better-wp-security' ) }
				value={
					search.actor_type && search.actor_id
						? search.actor_type + ':' + search.actor_id
						: search.actor_type
				}
				onChange={ ( change ) => {
					if ( change === '' ) {
						onSearch( { actor_type: '', actor_id: '' } );
					} else {
						const [ actorType, actorId = '' ] = change.split( ':' );
						onSearch( {
							actor_type: actorType,
							actor_id: actorId,
						} );
					}
				} }
			/>
			<SearchControl
				value={ search.search }
				onChange={ ( term ) => onSearch( { search: term } ) }
				isSearching={ isQuerying }
				surfaceVariant="secondary"
				placeholder={ __( 'Search Bans', 'better-wp-security' ) }
			/>
		</section>
	);
}
