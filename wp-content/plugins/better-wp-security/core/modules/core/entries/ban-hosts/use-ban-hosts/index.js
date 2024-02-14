/**
 * External dependencies
 */
import { useDebounceCallback } from '@react-hook/debounce';

/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';

export default function useBanHosts( queryId ) {
	const [ isCreating, setCreating ] = useState( false );
	const [ isSaving, setSaving ] = useState( false );
	const { isQuerying } = useSelect( ( select ) => ( {
		isQuerying: select( 'ithemes-security/bans' ).isQuerying( queryId ),
	} ), [ queryId ] );
	const {
		createBan,
		query,
	} = useDispatch( 'ithemes-security/bans' );
	const debouncedQuery = useDebounceCallback( query, 500 );
	const [ selected, select ] = useState( 0 );
	const onSelect = ( selectedId ) => {
		select( selectedId );
		setCreating( false );
	};

	const afterSave = () => {
		query( queryId, { per_page: 100 } );
	};

	useEffect( () => {
		query( queryId, { per_page: 100 } );
	}, [ query, queryId ] );

	return {
		isCreating,
		setCreating,
		isSaving,
		setSaving,
		isQuerying,
		createBan,
		afterSave,
		query: debouncedQuery,
		selected,
		onSelect,
	};
}
