<?php

use iThemesSecurity\Lib\Legacy_Password_Requirement;
use iThemesSecurity\Lib\Password_Requirement;
use iThemesSecurity\User_Groups;

/**
 * Class ITSEC_Lib_Password_Requirements
 */
class ITSEC_Lib_Password_Requirements {

	/** @var Password_Requirement[] */
	private static $requirements;

	/**
	 * Get all registered password requirements.
	 *
	 * @return Password_Requirement[]
	 */
	public static function get_registered() {
		if ( null === self::$requirements ) {
			self::$requirements = array();

			/**
			 * Fires when password requirements should be registered.
			 */
			do_action( 'itsec_register_password_requirements' );
		}

		return self::$requirements;
	}

	/**
	 * Register a password requirement.
	 *
	 * @param Password_Requirement|string $requirement_or_code
	 * @param array                       $opts
	 */
	public static function register( $requirement_or_code, $opts = [] ) {
		if ( $requirement_or_code instanceof Password_Requirement ) {
			self::$requirements[ $requirement_or_code->get_code() ] = $requirement_or_code;

			return;
		}

		_doing_it_wrong( __METHOD__, 'Pass a Password_Requirement instance instead of a configuration array.', '7.0.0' );

		$reason_code = $requirement_or_code;

		$merged = wp_parse_args( $opts, array(
			'evaluate'                => null,
			'validate'                => null,
			'flag_check'              => null,
			'reason'                  => null,
			'defaults'                => null,
			'settings_config'         => null, // Callable returning label, description, render & sanitize callbacks.
			'meta'                    => "_itsec_password_evaluation_{$reason_code}",
			'evaluate_if_not_enabled' => false,
		) );

		if (
			( array_key_exists( 'validate', $opts ) || array_key_exists( 'evaluate', $opts ) ) &&
			( ! is_callable( $merged['validate'] ) || ! is_callable( $merged['evaluate'] ) )
		) {
			_doing_it_wrong( __METHOD__, 'Validate and evaluate must be callable if defined.', '5.8.0' );

			return;
		}

		if ( array_key_exists( 'flag_check', $opts ) && ! is_callable( $merged['flag_check'] ) ) {
			_doing_it_wrong( __METHOD__, 'Flag check must be callable if defined.', '5.8.0' );

			return;
		}

		if ( array_key_exists( 'defaults', $opts ) ) {
			if ( ! is_array( $merged['defaults'] ) ) {
				_doing_it_wrong( __METHOD__, 'Defaults must be an array if defined.', '5.8.0' );

				return;
			}

			if ( ! array_key_exists( 'settings_config', $opts ) ) {
				_doing_it_wrong( __METHOD__, 'Settings config must be defined if defaults are provided.', '5.8.0' );

				return;
			}
		}

		if ( array_key_exists( 'settings_config', $opts ) && ! is_callable( $merged['settings_config'] ) ) {
			_doing_it_wrong( __METHOD__, 'Settings config must be a callable if defined.', '5.8.0' );

			return;
		}

		self::$requirements[ $reason_code ] = new Legacy_Password_Requirement( $reason_code, $merged );
	}

	/**
	 * Get a message indicating to the user why a password change is required.
	 *
	 * @param WP_User $user
	 *
	 * @return string
	 */
	public static function get_message_for_password_change_reason( $user ) {

		if ( ! $reason = self::password_change_required( $user ) ) {
			return '';
		}

		$message = '';

		$registered = self::get_registered();

		if ( isset( $registered[ $reason ] ) ) {
			$settings   = self::get_requirement_settings( $reason );
			$evaluation = get_user_meta( $user->ID, $registered[ $reason ]->get_meta_key(), true );
			$message    = $registered[ $reason ]->get_reason_message( $evaluation, $settings );
		}

		/**
		 * Retrieve a human readable description as to why a password change has been required for the current user.
		 *
		 * Modules MUST HTML escape their reason strings before returning them with this filter.
		 *
		 * @param string  $message
		 * @param WP_User $user
		 */
		$message = apply_filters( "itsec_password_change_requirement_description_for_{$reason}", $message, $user );

		if ( $message ) {
			return $message;
		}

		return esc_html__( 'A password change is required for your account.', 'better-wp-security' );
	}

	/**
	 * Validate a user's password.
	 *
	 * @param WP_User|stdClass|int $user
	 * @param string               $new_password
	 * @param array                $args
	 *
	 * @return WP_Error Error object with new errors.
	 */
	public static function validate_password( $user, $new_password, $args = array() ) {

		$args = wp_parse_args( $args, array(
			'error'   => new WP_Error(),
			'context' => '',
		) );

		/** @var WP_Error $error */
		$error = $args['error'];
		$user  = $user instanceof stdClass ? $user : ITSEC_Lib::get_user( $user );

		if ( ! $user ) {
			$error->add( 'invalid_user', esc_html__( 'Invalid User', 'better-wp-security' ) );

			return $error;
		}

		if ( ! empty( $user->ID ) && wp_check_password( $new_password, get_userdata( $user->ID )->user_pass, $user->ID ) ) {
			$message = wp_kses( __( '<strong>ERROR</strong>: The password you have chosen appears to have been used before. You must choose a new password.', 'better-wp-security' ), array( 'strong' => array() ) );
			$error->add( 'pass', $message );

			return $error;
		}

		ITSEC_Lib::load( 'canonical-roles' );

		if ( isset( $args['role'] ) && $user instanceof WP_User ) {
			$canonical = ITSEC_Lib_Canonical_Roles::get_canonical_role_from_role_and_user( $args['role'], $user );
			$target    = User_Groups\Match_Target::for_user( $user, $args['role'] );
		} elseif ( isset( $args['role'] ) ) {
			$target    = User_Groups\Match_Target::for_role( $args['role'] );
			$canonical = ITSEC_Lib_Canonical_Roles::get_canonical_role_from_role( $args['role'] );
		} elseif ( empty( $user->ID ) || ! is_numeric( $user->ID ) ) {
			$args['role'] = get_option( 'default_role', 'subscriber' );
			$target       = User_Groups\Match_Target::for_role( $args['role'] );
			$canonical    = ITSEC_Lib_Canonical_Roles::get_canonical_role_from_role( $args['role'] );
		} else {
			$target    = User_Groups\Match_Target::for_user( get_userdata( $user->ID ) );
			$canonical = ITSEC_Lib_Canonical_Roles::get_user_role( $user );
		}

		$args['canonical'] = $canonical;
		$args['target']    = $target;

		/**
		 * Fires when modules should validate a password according to their rules.
		 *
		 * @since 3.9.0
		 *
		 * @param \WP_Error         $error
		 * @param \WP_User|stdClass $user
		 * @param string            $new_password
		 * @param array             $args
		 */
		do_action( 'itsec_validate_password', $error, $user, $new_password, $args );

		return $error;
	}

	/**
	 * Flag that a password change is required for a user.
	 *
	 * @param WP_User|int $user
	 * @param string      $reason
	 */
	public static function flag_password_change_required( $user, $reason ) {
		$user = ITSEC_Lib::get_user( $user );

		if ( $user ) {
			update_user_meta( $user->ID, 'itsec_password_change_required', $reason );
		}
	}

	/**
	 * Check if a password change is required for the given user.
	 *
	 * @param WP_User|int $user
	 *
	 * @return string|false Either the reason code a change is required, or false.
	 */
	public static function password_change_required( $user ) {
		$user = ITSEC_Lib::get_user( $user );

		if ( ! $user ) {
			return false;
		}

		$reason = get_user_meta( $user->ID, 'itsec_password_change_required', true );

		if ( ! $reason ) {
			return false;
		}

		$registered = self::get_registered();

		if ( isset( $registered[ $reason ] ) ) {
			return self::is_requirement_enabled( $reason ) ? $reason : false;
		}

		if ( ! has_filter( "itsec_password_change_requirement_description_for_{$reason}" ) ) {
			return false;
		}

		return $reason;
	}

	/**
	 * Globally clear all required password changes with a particular reason code.
	 *
	 * @param string $reason
	 */
	public static function global_clear_required_password_change( $reason ) {
		delete_metadata( 'user', 0, 'itsec_password_change_required', $reason, true );
	}

	/**
	 * Get the GMT time the user's password has last been changed.
	 *
	 * @param WP_User|int $user
	 *
	 * @return int
	 */
	public static function password_last_changed( $user ) {

		$user = ITSEC_Lib::get_user( $user );

		if ( ! $user ) {
			return 0;
		}

		$changed    = (int) get_user_meta( $user->ID, 'itsec_last_password_change', true );
		$deprecated = (int) get_user_meta( $user->ID, 'itsec-password-updated', true );

		if ( $deprecated > $changed ) {
			return $deprecated;
		}

		if ( ! $changed ) {
			return strtotime( $user->user_registered );
		}

		return $changed;
	}

	/**
	 * Is a password requirement enabled.
	 *
	 * @param string $requirement
	 *
	 * @return bool
	 */
	public static function is_requirement_enabled( $requirement ) {

		$requirements = self::get_registered();

		if ( ! isset( $requirements[ $requirement ] ) ) {
			return false;
		}

		if ( $requirements[ $requirement ]->is_always_enabled() ) {
			return true;
		}

		if ( $requirements[ $requirement ]->has_user_group() ) {
			return ! empty( self::get_requirement_settings( $requirement )['group'] );
		}

		$enabled = ITSEC_Modules::get_setting( 'password-requirements', 'enabled_requirements' );

		if ( ! empty( $enabled[ $requirement ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get requirement settings.
	 *
	 * @param string $requirement
	 *
	 * @return array
	 */
	public static function get_requirement_settings( $requirement ) {

		$requirements = self::get_registered();

		if ( ! isset( $requirements[ $requirement ] ) ) {
			return [];
		}

		if ( ! $requirements[ $requirement ]->get_settings_schema() ) {
			return [];
		}

		$all_settings = ITSEC_Modules::get_setting( 'password-requirements', 'requirement_settings' );

		return $all_settings[ $requirement ] ?? [];
	}
}
