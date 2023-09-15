<?php

use iThemesSecurity\User_Groups\Matcher;
use iThemesSecurity\User_Groups;

final class ITSEC_Application_Passwords_Util {
	const USERMETA_KEY_APPLICATION_PASSWORDS = '_application_passwords';

	public static function handle_ajax_request() {
		_deprecated_function( __METHOD__, '7.0.0' );
		wp_send_json_error( new WP_Error( 'itsec-application-passwords-invalid-ajax-request', esc_html__( 'ITSEC_Application_Passwords_Util::handle_request() was triggered by an invalid action.', 'better-wp-security' ) ) );
	}

	public static function create( $user_id, $name, $enabled_for, $rest_api_permissions ) {
		_deprecated_function( __METHOD__, '7.0.0', 'WP_Application_Passwords::create_new_application_password' );

		if ( empty( $name ) ) {
			return new WP_Error( 'itsec-application-passwords-util-create-empty-name', esc_html__( 'You must supply a name for this application password.', 'better-wp-security' ) );
		}


		$enabled_for = (array) $enabled_for;

		if ( empty( $enabled_for ) ) {
			return new WP_Error( 'itsec-application-passwords-util-create-empty-enabled_for-arg', esc_html__( 'An application password must be valid for either REST API requests, XML-RPC requests, or both.', 'better-wp-security' ) );
		} else {
			$unrecognized_types = array_diff( $enabled_for, array( 'rest-api', 'xml-rpc' ) );

			if ( ! empty( $unrecognized_types ) ) {
				return new WP_Error( 'itsec-application-passwords-util-create-invalid-enabled_for-value', esc_html__( 'Received an invalid request type to be associated with this application password. Please reload this page and try again.', 'better-wp-security' ) );
			}
		}


		if ( in_array( 'rest-api', $enabled_for ) ) {
			if ( empty( $rest_api_permissions ) ) {
				return new WP_Error( 'itsec-application-passwords-util-create-empty-rest_api_permissions-arg', esc_html__( 'You must select either "Read and write" or "Read-only".', 'better-wp-security' ) );
			} elseif ( ! in_array( $rest_api_permissions, array( 'read', 'write' ) ) ) {
				return new WP_Error( 'itsec-application-passwords-util-create-invalid-rest_api_permissions-value', esc_html__( 'Received invalid rest_api_permissions for this application password. Please reload this page and try again.', 'better-wp-security' ) );
			}
		} else {
			$rest_api_permissions = '';
		}


		$password        = wp_generate_password( 16, false );
		$hashed_password = wp_hash_password( $password );

		$item = array(
			'name'                 => $name,
			'enabled_for'          => $enabled_for,
			'rest_api_permissions' => $rest_api_permissions,
			'password'             => $hashed_password,
			'created'              => time(),
			'last_used'            => null,
			'last_ip'              => null,
		);

		$passwords   = self::get( $user_id );
		$passwords[] = $item;

		self::set( $user_id, $passwords );

		$retval = array(
			'user_id'  => $user_id,
			'item'     => $item,
			'password' => $password,
		);

		return $retval;
	}

	public static function revoke( $user_id, $slug ) {
		_deprecated_function( __METHOD__, '7.0.0', 'WP_Application_Passwords::delete_application_password' );

		$passwords = self::get( $user_id );

		foreach ( $passwords as $key => $item ) {
			if ( self::get_unique_slug( $item ) === $slug ) {
				unset( $passwords[ $key ] );
				self::set( $user_id, $passwords );

				return true;
			}
		}

		return new WP_Error( 'itsec-application-passwords-util-revoke-invalid-slug', esc_html__( 'Unable to find the requested application password. Please reload this page and try again.', 'better-wp-security' ) );
	}

	public static function revoke_all( $user_id ) {
		_deprecated_function( __METHOD__, '7.0.0', 'WP_Application_Passwords::delete_all_application_passwords' );

		$passwords = self::get( $user_id );

		if ( ! empty( $passwords ) ) {
			self::set( $user_id, array() );

			return sizeof( $passwords );
		}

		return 0;
	}

	public static function show_user_profile( $user ) {
		_deprecated_function( __METHOD__, '7.0.0' );
	}

	public static function get( $user_id ) {
		_deprecated_function( __METHOD__, '7.0.0', 'WP_Application_Passwords::get_user_application_passwords' );
		$items = get_user_meta( $user_id, self::USERMETA_KEY_APPLICATION_PASSWORDS, true );

		if ( ! is_array( $items ) ) {
			$items = array();
		}

		foreach ( $items as $index => $item ) {
			if ( empty( $item['enabled_for'] ) ) {
				$items[ $index ]['enabled_for'] = array( 'rest-api', 'xml-rpc' );
			}

			if ( empty( $item['rest_api_permissions'] ) ) {
				$items[ $index ]['rest_api_permissions'] = 'write';
			}
		}

		return $items;
	}

	public static function get_unique_slug( $item ) {
		_deprecated_function( __METHOD__, '7.0.0' );

		$concat = $item['name'] . '|' . $item['password'] . '|' . $item['created'];
		$hash   = md5( $concat );

		return substr( $hash, 0, 12 );
	}

	public static function chunk_password( $raw_password ) {
		_deprecated_function( __METHOD__, '7.0.0', 'WP_Application_Passwords::chunk_password' );

		$raw_password = preg_replace( '/[^a-z\d]/i', '', $raw_password );

		return trim( chunk_split( $raw_password, 4, ' ' ) );
	}

	public static function set( $user_id, $passwords ) {
		_deprecated_function( __METHOD__, '7.0.0', 'WP_Application_Passwords::set_user_application_passwords' );

		return update_user_meta( $user_id, self::USERMETA_KEY_APPLICATION_PASSWORDS, $passwords );
	}

	public static function get_table_columns() {
		_deprecated_function( __METHOD__, '7.0.0' );

		return array(
			'name'                 => esc_html__( 'Name', 'better-wp-security' ),
			'enabled_for'          => esc_html__( 'API Types', 'better-wp-security' ),
			'rest_api_permissions' => esc_html__( 'REST API Permissions', 'better-wp-security' ),
			'created'              => esc_html__( 'Created', 'better-wp-security' ),
			'last_used'            => esc_html__( 'Last Used', 'better-wp-security' ),
			'last_ip'              => esc_html__( 'Last IP', 'better-wp-security' ),
			'revoke'               => esc_html__( 'Revoke', 'better-wp-security' ),
		);
	}

	public static function get_table_column_entry( $item, $column_name ) {
		_deprecated_function( __METHOD__, '7.0.0' );

		switch ( $column_name ) {
			case 'name':
				return esc_html( $item['name'] );
			case 'enabled_for':
				if ( array( 'xml-rpc' ) === $item['enabled_for'] ) {
					return esc_html__( 'XML-RPC', 'better-wp-security' );
				} elseif ( array( 'rest-api' ) === $item['enabled_for'] ) {
					return esc_html__( 'REST API', 'better-wp-security' );
				} else {
					return esc_html__( 'REST API and XML-RPC', 'better-wp-security' );
				}
			case 'rest_api_permissions':
				if ( ! in_array( 'rest-api', $item['enabled_for'] ) ) {
					return '&mdash;';
				} elseif ( 'read' === $item['rest_api_permissions'] ) {
					return esc_html__( 'Read-Only', 'better-wp-security' );
				} else {
					return esc_html__( 'Read and Write', 'better-wp-security' );
				}
			case 'created':
				if ( empty( $item['created'] ) ) {
					return '&mdash;';
				}

				return date( get_option( 'date_format', 'r' ), $item['created'] );
			case 'last_used':
				if ( empty( $item['last_used'] ) ) {
					return '&mdash;';
				}

				return date( get_option( 'date_format', 'r' ), $item['last_used'] );
			case 'last_ip':
				if ( empty( $item['last_ip'] ) ) {
					return '&mdash;';
				}

				return $item['last_ip'];
			case 'revoke':
				return '<input type="button" class="button delete itsec-application-password-revoke" value="' . esc_html__( 'Revoke', 'better-wp-security' ) . '" />';
			default:
				return 'ERROR: Not Set';
		}
	}

	/**
	 * Is Application Passwords available for the given user.
	 *
	 * @param int|WP_User $user
	 *
	 * @return bool
	 */
	public static function available_for_user( $user ) {
		_deprecated_function( __METHOD__, '7.0.0', 'wp_is_application_passwords_available_for_user' );

		if ( ! $user = ITSEC_Lib::get_user( $user ) ) {
			return false;
		}

		/** @var User_Groups\Matcher $matcher */
		$matcher = ITSEC_Modules::get_container()->get( Matcher::class );

		return $matcher->matches(
			User_Groups\Match_Target::for_user( $user ),
			ITSEC_Modules::get_setting( 'two-factor', 'application_passwords_group' )
		);
	}
}
