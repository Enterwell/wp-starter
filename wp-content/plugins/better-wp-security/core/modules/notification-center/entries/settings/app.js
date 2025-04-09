/**
 * External dependencies
 */
import { reduce } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { useCallback, useEffect } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { useAsync } from '@ithemes/security-hocs';
import { Page as RegisterPage } from '@ithemes/security.pages.settings';
import { STORE_NAME as SEARCH_STORE_NAME } from '@ithemes/security-search';
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import { Page } from './components';
import './style.scss';

const fetchNotifications = () => () =>
	apiFetch( {
		path: '/ithemes-security/rpc/notification-center/notifications',
	} );
const fetchUsersAndRoles = () =>
	apiFetch( {
		path: '/ithemes-security/rpc/notification-center/available-users-roles',
	} );

export default function App() {
	const modules = useSelect( ( select ) =>
		select( MODULES_STORE_NAME ).getActiveModules()
	);
	const asyncNotifications = useAsync(
		// eslint-disable-next-line react-hooks/exhaustive-deps
		useCallback( fetchNotifications(), [ modules ] )
	);
	const asyncUsersAndRoles = useAsync( fetchUsersAndRoles );
	useSearchProviders( asyncNotifications.value );

	return (
		<RegisterPage
			id="notification-center"
			title={ __( 'Notifications', 'better-wp-security' ) }
			icon="email-alt"
			priority={ 20 }
			roots={ [ 'onboard', 'import', 'settings' ] }
			key={ asyncNotifications.status + asyncUsersAndRoles.status }
		>
			{ () => (
				<Page
					asyncNotifications={ asyncNotifications }
					asyncUsersAndRoles={ asyncUsersAndRoles }
				/>
			) }
		</RegisterPage>
	);
}

function useSearchProviders( notifications ) {
	const { registerProvider } = useDispatch( SEARCH_STORE_NAME );

	useEffect( () => {
		registerProvider(
			'notifications',
			__( 'Notifications', 'better-wp-security' ),
			50,
			( { evaluate, results } ) => {
				return reduce(
					notifications,
					( count, notification, slug ) => {
						if (
							! evaluate.stringMatch( notification.l10n.label ) &&
							! evaluate.stringMatch(
								notification.l10n.description
							)
						) {
							return count;
						}

						results.items.push( {
							title: notification.l10n.label,
							description: notification.l10n.description,
							route: `/settings/notification-center/${ slug }`,
						} );

						return count++;
					},
					0
				);
			}
		);
	}, [ notifications, registerProvider ] );
}
