<?php

register_rest_route( 'ithemes-security/rpc', '/notification-center/notifications', [
	'callback' => function () {
		$nc = ITSEC_Core::get_notification_center();

		$notifications = array_map( function ( $notification ) use ( $nc ) {
			$notification['l10n'] = $nc->get_notification_strings( $notification['slug'] );

			if ( is_array( $notification['schedule'] ) ) {
				$options = ITSEC_Modules::get_validator( 'notification-center' )->get_schedule_options( $notification['schedule'] );

				$notification['l10n']['schedule'] = [];

				foreach ( $options as $value => $label ) {
					$notification['l10n']['schedule'][] = compact( 'value', 'label' );
				}
			}

			return $notification;
		}, $nc->get_notifications() );

		return wp_list_sort( $notifications, 'module', 'ASC', true );
	},

	'permission_callback' => 'ITSEC_Core::current_user_can_manage',
] );

register_rest_route( 'ithemes-security/rpc', '/notification-center/available-users-roles', [
	'callback' => function () {
		$users_and_roles = ITSEC_Modules::get_validator( 'notification-center' )->get_available_admin_users_and_roles();

		return [
			'users' => array_map( function ( $user_id, $label ) {
				return [
					'value' => (int) $user_id,
					'label' => $label,
				];
			}, array_keys( $users_and_roles['users'] ), array_values( $users_and_roles['users'] ) ),
			'roles' => array_map( function ( $role, $label ) {
				return [
					'value' => $role,
					'label' => $label,
				];
			}, array_keys( $users_and_roles['roles'] ), array_values( $users_and_roles['roles'] ) ),
		];
	},

	'permission_callback' => 'ITSEC_Core::current_user_can_manage',
] );
