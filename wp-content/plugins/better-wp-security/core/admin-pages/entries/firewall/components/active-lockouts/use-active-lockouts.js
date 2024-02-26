/**
 * WordPress dependencies
 */
import { useCallback, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies
 */
import useBanLockoutFirewallPage from './use-ban-lockout';
import useReleaseLockoutFirewallPage from './use-release-lockout';
import useFetchLockouts from './use-fetch-lockouts';

export default function useActiveLockouts() {
	const [ selectedId, setSelectedId ] = useState( 0 );
	const [ searchTerm, setSearchTerm ] = useState( '' );
	const [ banningIds, banLockoutFirewallPage ] = useBanLockoutFirewallPage( selectedId );
	const [ releasingIds, releaseLockoutFirewallPage ] = useReleaseLockoutFirewallPage( selectedId );
	const { getLockouts, isQuerying, value: lockouts, error: getLockoutsError } = useFetchLockouts( searchTerm );

	const select = ( id ) => {
		return setSelectedId( id );
	};

	const getDetails = useCallback( ( lockout ) => {
		if ( ! lockout._links.self[ 0 ].href ) {
			return Promise.reject( new Error( 'No data available.' ) );
		}

		const url = addQueryArgs( lockout._links.self[ 0 ].href, { context: 'edit' } );
		return apiFetch( { url } ).then( ( response ) => {
			return response.detail;
		} );
	}, [] );

	const onBan = async ( e ) => {
		e.preventDefault();
		const banned = await banLockoutFirewallPage( selectedId );

		if ( banned ) {
			setSelectedId( ( currentSelectedId ) => currentSelectedId === selectedId ? 0 : currentSelectedId );
			getLockouts( searchTerm );
		}
	};

	const onRelease = async ( e ) => {
		e.preventDefault();
		const released = await releaseLockoutFirewallPage( selectedId );

		if ( released ) {
			setSelectedId( ( currentSelectedId ) => currentSelectedId === selectedId ? 0 : currentSelectedId );
			getLockouts( searchTerm );
		}
	};

	return {
		selectedId,
		searchTerm,
		setSearchTerm,
		banningIds,
		releasingIds,
		lockouts,
		getLockoutsError,
		isQuerying,
		select,
		getDetails,
		onBan,
		onRelease,
	};
}
