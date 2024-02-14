/**
 * WordPress dependencies
 */
import { useCallback, useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import useBanLockout from '../use-ban-lockout';
import useReleaseLockout from '../use-release-lockout';

export default function useActiveLockouts( card ) {
	const [ selectedId, setSelectedId ] = useState( 0 );
	const [ searchTerm, setSearchTerm ] = useState( '' );
	const [ banningIds, banLockout, isBanAvailable ] = useBanLockout( card );
	const [ releasingIds, releaseLockout, isReleaseAvailable ] = useReleaseLockout( card );

	const getDetails = useCallback( ( lockout ) => {
		if ( ! lockout.links.item ) {
			return Promise.reject( new Error( 'No data available.' ) );
		}

		const url = lockout.links.item[ 0 ].href.replace(
			'{lockout_id}',
			lockout.id
		);

		return apiFetch( { url } ).then( ( response ) => {
			return response.detail;
		} );
	}, [] );

	const { isQuerying } = useSelect(
		( select ) => ( {
			isQuerying: select( 'ithemes-security/dashboard' ).isQueryingDashboardCard( card.id ),
		} ),
		[ card.id ]
	);
	const { queryDashboardCard: query, refreshDashboardCard } = useDispatch( 'ithemes-security/dashboard' );
	const select = ( id ) => {
		return setSelectedId( id );
	};

	const onBan = async ( e ) => {
		e.preventDefault();
		const banned = await banLockout( selectedId );
		await refreshDashboardCard( card.id );

		if ( banned ) {
			setSelectedId( ( currentSelectedId ) => currentSelectedId === selectedId ? 0 : currentSelectedId );
		}
	};

	const onRelease = async ( e ) => {
		e.preventDefault();
		const released = await releaseLockout( selectedId );
		await refreshDashboardCard( card.id );

		if ( released ) {
			setSelectedId( ( currentSelectedId ) => currentSelectedId === selectedId ? 0 : currentSelectedId );
		}
	};

	return {
		selectedId,
		searchTerm,
		setSearchTerm,
		isQuerying,
		query,
		select,
		getDetails,
		onBan,
		onRelease,
		isBanAvailable,
		isReleaseAvailable,
		releasingIds,
		banningIds,
	};
}
