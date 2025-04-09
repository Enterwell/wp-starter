<?php

class ITSEC_Lib_Login {
	/**
	 * Get a user account by the user's provided identifier.
	 *
	 * @param string $identifier
	 *
	 * @return WP_User|null
	 */
	public static function get_user( $identifier ) {
		foreach ( self::get_user_lookup_fields() as $field ) {
			if ( $user = get_user_by( $field, $identifier ) ) {
				return $user;
			}
		}

		return null;
	}

	/**
	 * Get an identifier for accepted lookup field type.
	 *
	 * @param WP_User $user
	 *
	 * @return string
	 */
	public static function get_identifier_for_user( \WP_User $user ): string {
		foreach ( self::get_user_lookup_fields() as $field ) {
			return $user->get( 'user_' . $field );
		}

		throw new DomainException( __( 'Could not determine an identifier for the user.', 'better-wp-security' ) );
	}

	/**
	 * Get the fields a user can provide to identify their user account.
	 *
	 * @return array
	 */
	public static function get_user_lookup_fields() {
		$fields = array( 'login', 'email' );

		if ( ITSEC_Modules::is_active( 'wordpress-tweaks' ) ) {
			if ( 'email' === ITSEC_Modules::get_setting( 'wordpress-tweaks', 'valid_user_login_type' ) ) {
				$fields = array( 'email' );
			} elseif ( 'username' === ITSEC_Modules::get_setting( 'wordpress-tweaks', 'valid_user_login_type' ) ) {
				$fields = array( 'login' );
			}
		}

		return $fields;
	}

	/**
	 * Get the input label for the lookup field.
	 *
	 * @return string
	 */
	public static function get_user_lookup_fields_label() {
		$fields = self::get_user_lookup_fields();

		if ( count( $fields ) === 2 ) {
			return esc_html__( 'Username or Email Address', 'better-wp-security' );
		}

		if ( 'email' === $fields[0] ) {
			return esc_html__( 'Email Address', 'better-wp-security' );
		}

		return esc_html__( 'Username', 'better-wp-security' );
	}

	/**
	 * Gets an error message indicating that the requested
	 * user could not be found.
	 *
	 * @return string
	 */
	public static function get_not_found_error_message(): string {
		$fields = self::get_user_lookup_fields();

		if ( count( $fields ) === 2 ) {
			return __( 'Could not find a user with that username or email address', 'better-wp-security' );
		}

		if ( 'email' === $fields[0] ) {
			return __( 'Could not find a user with that email address', 'better-wp-security' );
		}

		return __( 'Could not find a user with that username', 'better-wp-security' );
	}
}
