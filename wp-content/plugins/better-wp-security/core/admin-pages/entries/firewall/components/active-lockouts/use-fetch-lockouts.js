import { useCallback, useMemo } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { useAsync } from '@ithemes/security-hocs';

export default function useFetchLockouts( searchTerm ) {
	const queryArgs = useMemo( () => {
		return {
			search: searchTerm,
		};
	}, [ searchTerm ] );
	const execute = useCallback( async () => {
		try {
			return await apiFetch( {
				path: addQueryArgs(
					'/ithemes-security/v1/lockouts',
					queryArgs
				),
			} );
		} catch ( e ) {
			return false;
		}
	}, [ queryArgs ] );

	const { execute: getLockouts, status, value, error } = useAsync( execute );
	return { getLockouts, status, value, error };
}
