/**
 * External dependencies
 */
import { useRouteMatch, useParams, Route, Switch } from 'react-router-dom';
import { map } from 'lodash';

/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import {
	ChildPages,
} from '@ithemes/security.pages.settings';
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import { Settings, Notification, Onboard } from '../';

export default function Page( { asyncNotifications, asyncUsersAndRoles } ) {
	const { url, path, params: { root } } = useRouteMatch();

	const {
		status: notificationsStatus,
		value: notifications,
	} = asyncNotifications;
	const {
		status: usersAndRolesStatus,
		value: usersAndRoles,
	} = asyncUsersAndRoles;

	const error = useSelect(
		( select ) =>
			select( MODULES_STORE_NAME ).getError( 'notification-center' ),
		[]
	);

	if (
		( notificationsStatus !== 'success' &&
			( notificationsStatus !== 'pending' || ! notifications ) ) ||
		usersAndRolesStatus !== 'success'
	) {
		return null;
	}

	const nav = (
		<ChildPages
			pages={ map( notifications, ( notification ) => ( {
				title: notification.l10n.label,
				to: `${ url }/${ notification.slug }`,
				id: notification.slug,
			} ) ) }
		/>
	);

	return (
		<Switch>
			<Route path={ `${ path }/:child` }>
				{ nav }
				<NotificationPage
					notifications={ notifications }
					usersAndRoles={ usersAndRoles }
					apiError={ error }
				/>
			</Route>
			<Route path={ path }>
				{ nav }
				{ root === 'settings' && (
					<Settings
						usersAndRoles={ usersAndRoles }
						apiError={ error }
					/>
				) }
				{ root !== 'settings' && (
					<Onboard usersAndRoles={ usersAndRoles } apiError={ error } />
				) }
			</Route>
		</Switch>
	);
}

function NotificationPage( {
	notifications,
	usersAndRoles,
	apiError,
} ) {
	const { child: notification } = useParams();

	const { settings } = useSelect(
		( select ) => ( {
			settings: select( MODULES_STORE_NAME ).getEditedSetting(
				'notification-center',
				'notifications'
			),
		} ),
		[]
	);
	const { editSetting } = useDispatch(
		MODULES_STORE_NAME
	);

	const onChange = useCallback(
		( change ) =>
			editSetting( 'notification-center', 'notifications', {
				...settings,
				[ notification ]: change,
			} ),
		[ notification, editSetting, settings ]
	);

	if ( ! settings ) {
		return null;
	}

	return (
		<Notification
			notification={ notifications[ notification ] }
			usersAndRoles={ usersAndRoles }
			settings={ settings[ notification ] || {} }
			onChange={ onChange }
			apiError={ apiError }
		/>
	);
}
