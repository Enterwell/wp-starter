<?php

class ITSEC_Lib_Feature_Flags {

	/** @var array */
	private static $flags = array();

	/**
	 * Whether the Feature Flag UI should be displayed.
	 *
	 * @return bool
	 */
	public static function show_ui() {
		$show = count( self::get_enabled() ) > 0;

		if ( defined( 'ITSEC_SHOW_FEATURE_FLAGS' ) ) {
			$show = ITSEC_SHOW_FEATURE_FLAGS;
		}

		return apply_filters( 'itsec_show_feature_flags_ui', $show );
	}

	/**
	 * Register a feature flag.
	 *
	 * @param string $name
	 * @param array  $args
	 *
	 * @return true|WP_Error
	 */
	public static function register_flag( $name, $args = array() ) {
		if ( ! preg_match( '/^\w+$/', $name ) ) {
			return new WP_Error( 'invalid_flag_name', __( 'Invalid flag name.', 'better-wp-security' ) );
		}

		self::$flags[ $name ] = wp_parse_args( $args, array(
			'rate'          => false,
			'remote'        => false,
			'title'         => '',
			'description'   => '',
			'documentation' => '',
			'requirements'  => [],
		) );

		return true;
	}

	/**
	 * Is the given flag available to be enabled.
	 *
	 * @param string $flag
	 *
	 * @return bool
	 */
	public static function is_available( $flag ) {
		if ( ! $config = self::get_flag_config( $flag ) ) {
			return false;
		}

		if ( ! $config['requirements'] ) {
			return true;
		}

		$error = ITSEC_Lib::evaluate_requirements( $config['requirements'] );

		return ! $error->has_errors();
	}

	/**
	 * Get a list of all the available feature flags.
	 *
	 * @return array
	 */
	public static function get_available_flags() {
		$flags = self::get_registered_flags();

		return array_filter( $flags, [ __CLASS__, 'is_available' ], ARRAY_FILTER_USE_KEY );
	}

	/**
	 * Get a list of all registered flags.
	 *
	 * @return array
	 */
	public static function get_registered_flags() {
		$flags = array();

		foreach ( self::$flags as $flag => $_ ) {
			$flags[ $flag ] = self::get_flag_config( $flag );
		}

		return $flags;
	}

	/**
	 * Get a list of all the enabled feature flags.
	 *
	 * @return string[]
	 */
	public static function get_enabled() {
		$enabled = array();

		foreach ( self::get_available_flags() as $flag => $_ ) {
			if ( self::is_enabled( $flag ) ) {
				$enabled[] = $flag;
			}
		}

		return $enabled;
	}

	/**
	 * Check if a flag is enabled.
	 *
	 * @param string $flag
	 *
	 * @return bool
	 */
	public static function is_enabled( $flag ) {
		if ( ! $config = self::get_flag_config( $flag ) ) {
			return false;
		}

		if ( ! self::is_available( $flag ) ) {
			return false;
		}

		if ( defined( 'ITSEC_FF_' . $flag ) ) {
			// A constant overrules everything.
			return (bool) constant( 'ITSEC_FF_' . $flag );
		}

		if ( ! empty( $config['disabled'] ) ) {
			return false;
		}

		$enabled = ITSEC_Modules::get_setting( 'feature-flags', 'enabled' );

		if ( in_array( $flag, $enabled, true ) ) {
			// If the flag is set as enabled, then enable it.
			return true;
		}

		// If this is a gradual roll-out.
		if ( $rate = $config['rate'] ) {
			$rates    = ITSEC_Modules::get_setting( 'feature-flags', 'rates' );
			$opt_outs = ITSEC_Modules::get_setting( 'feature-flags', 'opt_outs' );

			// If the flag has been manually disabled with `ITSEC_Lib_Feature_Flags::disable()`, then exclude them from the feature.
			if ( in_array( $flag, $opt_outs, true ) ) {
				return false;
			}

			// If the dice haven't been rolled, or the rate has changed since the last run, roll the dice.
			if ( ! isset( $rates[ $flag ] ) || $rates[ $flag ] < $rate ) {
				$enabled = mt_rand( 1, 100 ) <= $rate;

				$settings = ITSEC_Modules::get_settings( 'feature-flags' );

				if ( $enabled ) {
					$settings['enabled'][] = $flag;
				}

				$settings['rates'][ $flag ] = $rate;

				ITSEC_Modules::set_settings( 'feature-flags', $settings );

				if ( $enabled ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get's the reason for the flag being enabled/disabled.
	 *
	 * @param string $flag
	 *
	 * @return array
	 */
	public static function get_reason( $flag ) {
		if ( ! $config = self::get_flag_config( $flag ) ) {
			return [ 'unknown', __( 'Unknown flag', 'better-wp-security' ) ];
		}

		if ( ! self::is_available( $flag ) ) {
			$evaluation = ITSEC_Lib::evaluate_requirements( $config['requirements'] );

			return [ 'requirements', $evaluation->get_error_message() ];
		}

		if ( defined( 'ITSEC_FF_' . $flag ) ) {
			return [ 'constant', __( 'Manually configured with a constant.', 'better-wp-security' ) ];
		}

		if ( ! empty( $config['disabled'] ) ) {
			return [ 'remote', __( 'Remotely disabled by SolidWP.', 'better-wp-security' ) ];
		}

		$rates = ITSEC_Modules::get_setting( 'feature-flags', 'rates' );

		if ( isset( $rates[ $flag ] ) ) {
			return [ 'rollout', __( 'Gradually rolling out.', 'better-wp-security' ) ];
		}

		return [ 'setting', __( 'Configured on the Feature Flags page.', 'better-wp-security' ) ];
	}

	/**
	 * Manually enable a feature flag.
	 *
	 * @param string $flag
	 */
	public static function enable( $flag ) {
		$enabled   = ITSEC_Modules::get_setting( 'feature-flags', 'enabled' );
		$enabled[] = $flag;
		ITSEC_Modules::set_setting( 'feature-flags', 'enabled', $enabled );
	}

	/**
	 * Manually disable a feature flag.
	 *
	 * @param string $flag
	 */
	public static function disable( $flag ) {
		$settings = ITSEC_Modules::get_settings( 'feature-flags' );

		$settings['opt_outs'][] = $flag;
		$settings['enabled']    = array_filter( $settings['enabled'], function ( $maybe_flag ) use ( $flag ) {
			return $maybe_flag !== $flag;
		} );

		ITSEC_Modules::set_settings( 'feature-flags', $settings );
	}

	/**
	 * Get the flag configuration.
	 *
	 * @param string $flag
	 *
	 * @return array|null
	 */
	public static function get_flag_config( $flag ) {
		if ( ! isset( self::$flags[ $flag ] ) ) {
			return null;
		}

		$config = self::$flags[ $flag ];

		if ( $config['remote'] && $remote = ITSEC_Lib_Remote_Messages::get_feature( $flag ) ) {
			$config = array_merge( $config, $remote );
		}

		return $config;
	}
}
