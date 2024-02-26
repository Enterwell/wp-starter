/**
 * WordPress dependencies
 */
import { useCallback, useState } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Hook that lets us manage releasing lockouts.
 *
 * @param {Object} card The Dashboard Card object.
 * @return {(number[]|(function(number): Promise<boolean>)|boolean)[]} A tuple of releasing ids, a callback to release a lockout and whether the feature is available.
 */
export default function useReleaseLockout( card ) {
	const [ releasingIds, setReleasingIds ] = useState( [] );
	const { createNotice, removeNotice } = useDispatch( 'core/notices' );

	const href = card._links[
		'ithemes-security:release-lockout'
	]?.[ 0 ].href;
	const isAvailable = !! href;
	const callback = useCallback( async ( lockoutId ) => {
		const url = href.replace( '{lockout_id}', lockoutId );
		const noticeId = `release-lockout-${ url }`;

		setReleasingIds( ( ids ) => [ ...ids, lockoutId ] );
		removeNotice( noticeId, 'ithemes-security' );

		try {
			await apiFetch( {
				url,
				method: 'DELETE',
			} );
			setTimeout( () => removeNotice( noticeId, 'ithemes-security' ), 5000 );
			createNotice(
				'success',
				__( 'Lockout Released', 'better-wp-security' ),
				{ id: noticeId, context: 'ithemes-security' }
			);

			return true;
		} catch ( e ) {
			createNotice(
				'error',
				sprintf(
					/* translators: 1. Error message */
					__( 'Error when releasing lockout: %s', 'better-wp-security' ),
					e.message || __( 'An unexpected error occurred.', 'better-wp-security' )
				),
				{ id: noticeId, context: 'ithemes-security' }
			);

			return false;
		} finally {
			setReleasingIds( ( ids ) => ids.filter( ( id ) => id !== lockoutId ) );
		}
	}, [ href, createNotice, removeNotice ] );

	return [ releasingIds, callback, isAvailable ];
}
