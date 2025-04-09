/**
 * WordPress dependencies
 */
import { useCallback, useState } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { __, sprintf } from '@wordpress/i18n';

export default function useBanLockoutFirewallPage( selectedId ) {
	const [ banningIds, setBanningIds ] = useState( [] );
	const { createNotice, removeNotice } = useDispatch( 'core/notices' );

	const callback = useCallback( async () => {
		const banUrl = '/ithemes-security/v1/lockouts/' + selectedId + '/ban-lockout';
		const noticeId = `ban-lockout-${ selectedId }`;

		setBanningIds( ( ids ) => [ ...ids, selectedId ] );
		removeNotice( noticeId, 'ithemes-security' );

		try {
			await apiFetch( {
				banUrl,
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
			setBanningIds( ( ids ) => ids.filter( ( id ) => id !== selectedId ) );
		}
	}, [ createNotice, removeNotice, selectedId ] );

	return [ banningIds, callback ];
}
