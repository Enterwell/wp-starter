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
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { HelpList } from '@ithemes/security-components';
import {
	ChildPages,
	useNavigation,
	HelpFill,
	PageHeader,
	Breadcrumbs,
} from '@ithemes/security.pages.settings';
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import { Settings, Notification } from '../';

export default function Page( { asyncNotifications, asyncUsersAndRoles } ) {
	const { url, path } = useRouteMatch();
	const { root } = useParams();
	const { goNext } = useNavigation();

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
	const { saveSettings } = useDispatch( MODULES_STORE_NAME );

	if (
		( notificationsStatus !== 'success' &&
			( notificationsStatus !== 'pending' || ! notifications ) ) ||
		usersAndRolesStatus !== 'success'
	) {
		return null;
	}

	const onSubmit =
		root === 'settings'
			? () => saveSettings( 'notification-center' )
			: goNext;
	const saveLabel =
		root === 'settings' ? undefined : __( 'Continue', 'better-wp-security' );
	const allowCleanSave = root !== 'settings';

	if ( 'onboard' === root ) {
		return (
			<Settings
				usersAndRoles={ usersAndRoles }
				onSubmit={ onSubmit }
				saveLabel={ saveLabel }
				allowUndo={ false }
				allowCleanSave={ allowCleanSave }
				apiError={ error }
			/>
		);
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
					onSubmit={ onSubmit }
					saveLabel={ saveLabel }
					allowCleanSave={ allowCleanSave }
				/>
			</Route>
			<Route path={ path }>
				{ nav }
				<Settings
					usersAndRoles={ usersAndRoles }
					onSubmit={ onSubmit }
					apiError={ error }
					saveLabel={ saveLabel }
					allowCleanSave={ allowCleanSave }
				/>
			</Route>
		</Switch>
	);
}

function NotificationPage( {
	notifications,
	usersAndRoles,
	apiError,
	onSubmit,
	saveLabel,
	allowCleanSave,
} ) {
	const { child: notification } = useParams();

	const { isDirty, isSaving, settings } = useSelect(
		( select ) => ( {
			isDirty: select( MODULES_STORE_NAME ).areSettingsDirty(
				'notification-center'
			),
			isSaving: select( MODULES_STORE_NAME ).isSavingSettings(
				'notification-center'
			),
			settings: select( MODULES_STORE_NAME ).getEditedSetting(
				'notification-center',
				'notifications'
			),
		} ),
		[]
	);
	const { editSetting, resetSettingEdits } = useDispatch(
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
		<>
			<Help notification={ notification } />
			<Notification
				notification={ notifications[ notification ] }
				usersAndRoles={ usersAndRoles }
				settings={ settings[ notification ] || {} }
				onChange={ onChange }
				isSaving={ isSaving }
				isDirty={ isDirty }
				onSubmit={ onSubmit }
				onUndo={ () => resetSettingEdits( 'notification-center' ) }
				saveLabel={ saveLabel }
				allowCleanSave={ allowCleanSave }
				apiError={ apiError }
			/>
		</>
	);
}

function Help( { notification } ) {
	const match = useRouteMatch();

	return (
		<HelpFill>
			<PageHeader
				title={ __( 'Notifications', 'better-wp-security' ) }
				breadcrumbs={
					<Breadcrumbs
						match={ match }
						title={ __( 'Help', 'better-wp-security' ) }
					/>
				}
			/>
			<HelpList
				topic={ `notification-center-${ notification }` }
				fallback="notification-center"
			/>
		</HelpFill>
	);
}
