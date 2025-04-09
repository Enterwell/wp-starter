/**
 * WordPress dependencies
 */
import { useCallback, useState } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Hook that lets us create a lockout from ban.
 *
 * @param {Object} card The Dashboard Card object.
 * @return {(number[]|(function(number): Promise<boolean>)|boolean)[]} A tuple of banning ids, a callback to ban a lockout and whether the feature is available.
 */
export default function useBanLockout( card ) {
	const [ banningIds, setBanningIds ] = useState( [] );
	const { createNotice, removeNotice } = useDispatch( 'core/notices' );
	const href = card._links[ 'ithemes-security:ban-lockout' ]?.[ 0 ].href;
	const isAvailable = !! href;
	const callback = useCallback( async ( lockoutId ) => {
		const url = href.replace( '{lockout_id}', lockoutId );
		const noticeId = `ban-lockout-${ url }`;

		setBanningIds( ( ids ) => [ ...ids, lockoutId ] );
		removeNotice( noticeId, 'ithemes-security' );

		try {
			await apiFetch( {
				url,
				method: 'POST',
			} );
			setTimeout( () => removeNotice( noticeId, 'ithemes-security' ), 5000 );
			createNotice(
				'success',
				__( 'Ban Created', 'better-wp-security' ),
				{ id: noticeId, context: 'ithemes-security' }
			);

			return true;
		} catch ( e ) {
			createNotice(
				'error',
				sprintf(
					/* translators: 1. Error message */
					__( 'Error when banning lockout: %s', 'better-wp-security' ),
					e.message || __( 'An unexpected error occurred.', 'better-wp-security' )
				),
				{ id: noticeId, context: 'ithemes-security' }
			);

			return false;
		} finally {
			setBanningIds( ( ids ) => ids.filter( ( id ) => id !== lockoutId ) );
		}
	}, [ href, createNotice, removeNotice ] );

	return [ banningIds, callback, isAvailable ];
}
