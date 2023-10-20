<?php

use iThemesSecurity\Module_Config;
use iThemesSecurity\User_Groups\Matchables_Source;

abstract class ITSEC_Validator {
	protected $run_validate_matching_fields = true;
	protected $run_validate_matching_types = true;

	/** @var Module_Config */
	protected $config;

	/** @var ITSEC_Settings */
	protected $settings_obj;

	/** @var array */
	protected $settings;

	/** @var array */
	protected $previous_settings;

	protected $can_save = true;
	protected $needs_refresh = false;
	protected $errors = array();
	protected $messages = array();
	protected $vars_to_skip_validate_matching_fields = array();
	protected $vars_to_skip_validate_matching_types = array();

	/**
	 * ITSEC_Validator constructor.
	 *
	 * @param Module_Config|null $config The configuration object. If omitted, will attempt to retrieve it.
	 */
	public function __construct( Module_Config $config = null ) {
		$this->config       = $config ?: ITSEC_Modules::get_config( $this->get_id() );
		$this->settings_obj = ITSEC_Modules::get_settings_obj( $this->get_id() );
	}

	abstract public function get_id();

	protected function sanitize_settings() { }

	protected function validate_settings() { }

	public function validate( $settings ) {
		$this->settings          = $settings;
		$this->previous_settings = ITSEC_Modules::get_settings( $this->get_id() );

		$this->can_save = true;
		$this->errors   = [];
		$this->messages = [];

		$this->sanitize_settings();

		if ( $this->run_validate_matching_fields ) {
			$this->validate_matching_fields();
		}

		if ( $this->run_validate_matching_types ) {
			$this->validate_matching_types();
		}

		$this->validate_settings();
	}

	protected function validate_matching_fields() {
		$id = $this->get_id();

		foreach ( $this->settings_obj->get_known_settings() as $name ) {
			if ( ! array_key_exists( $name, $this->settings ) && ! in_array( $name, $this->vars_to_skip_validate_matching_fields, true ) ) {
				$this->add_error( new WP_Error( "itsec-validator-$id-validate_matching_fields-missing-name-$name", sprintf( __( 'A validation function for %1$s received data that did not have the required entry for %2$s.', 'better-wp-security' ), $id, $name ) ) );
				$this->set_can_save( false );
			}
		}

		foreach ( $this->settings as $name => $value ) {
			if ( ! $this->settings_obj->is_known_setting( $name ) && ! in_array( $name, $this->vars_to_skip_validate_matching_fields, true ) && ! $this->is_extended_setting_from_inactive_module( $name ) ) {
				$this->add_error( new WP_Error( "itsec-validator-$id-validate_matching_fields-unknown-name-$name", sprintf( __( 'A validation function for %1$s received data that has an entry for %2$s when no such entry exists.', 'better-wp-security' ), $id, $name ) ) );
				$this->set_can_save( false );
			}
		}
	}

	protected function validate_matching_types() {
		$id = $this->get_id();

		foreach ( $this->settings as $name => $value ) {
			if ( in_array( $name, $this->vars_to_skip_validate_matching_types, true ) || ! $this->settings_obj->is_known_setting( $name ) ) {
				// This is to prevent errors for a specific var appearing twice.
				continue;
			}

			$default = $this->settings_obj->get_default( $name );

			if ( gettype( $default ) !== gettype( $value ) ) {
				$this->add_error(
					new WP_Error(
						"itsec-validator-$id-validate_matching_types-inmatching-type-$name",
						sprintf(
							__( 'A validation function for %1$s received data that does not match the expected data type for the %2$s entry. A data type of %3$s was expected, but a data type of %4$s was received.', 'better-wp-security' ),
							$id,
							$name,
							gettype( $default ),
							gettype( $value )
						)
					)
				);
				$this->set_can_save( false );
			}
		}
	}

	/**
	 * Checks if the given setting is from a now deactivated module that extends this module.
	 *
	 * This allow for preserving settings from pro modules if the user downgrades to free.
	 *
	 * @param string $setting
	 *
	 * @return bool
	 */
	final protected function is_extended_setting_from_inactive_module( string $setting ): bool {
		$extended = ITSEC_Storage::get( '__extended' );

		if ( empty( $extended[ $this->get_id() ] ) ) {
			return false;
		}

		return in_array( $setting, $extended[ $this->get_id() ], true );
	}

	final protected function set_default_if_empty( $vars ) {
		foreach ( (array) $vars as $var ) {
			if ( ! isset( $this->settings[ $var ] ) || '' === $this->settings[ $var ] ) {
				$this->settings[ $var ] = $this->settings_obj->get_default( $var );
			}
		}
	}

	final protected function set_previous_if_empty( $vars ) {
		foreach ( (array) $vars as $var ) {
			if ( ! isset( $this->settings[ $var ] ) || '' === $this->settings[ $var ] ) {
				$this->settings[ $var ] = $this->previous_settings[ $var ];
			}
		}
	}

	final protected function set_previous_if_missing( $vars ) {
		foreach ( (array) $vars as $var ) {
			if ( ! isset( $this->settings[ $var ] ) ) {
				$this->settings[ $var ] = $this->previous_settings[ $var ];
			}
		}
	}

	final protected function preserve_setting_if_exists( $vars ) {
		foreach ( (array) $vars as $var ) {
			if ( array_key_exists( $var, $this->previous_settings ) && ( ! isset( $this->settings[ $var ] ) || '' === $this->settings[ $var ] ) ) {
				$this->settings[ $var ] = $this->previous_settings[ $var ];
			}
		}
	}

	/**
	 * Sanitizes a setting.
	 *
	 * @param string|callable $type                  The sanitization to apply.
	 * @param string          $var                   The variable name.
	 * @param string          $name                  The human facing label for the setting.
	 * @param bool            $prevent_save_on_error Whether to prevent the module from saving if sanitization fails.
	 * @param bool            $trim_value            Whether to trim the value if it is a string.
	 * @param string          $custom_error          A custom error message to use instead of the default.
	 *
	 * @return bool Whether sanitization passed or not.
	 */
	final protected function sanitize_setting( $type, $var, $name, $prevent_save_on_error = true, $trim_value = true, $custom_error = '' ) {
		$id = $this->get_id();

		if ( ! isset( $this->settings[ $var ] ) ) {
			$this->add_error( new WP_Error( "itsec-validator-missing-var-$id-$var", sprintf( __( 'A validation check for %1$s failed. The %2$s value is missing. This could be due to a problem with the Solid Security installation or an invalid modification. Please reinstall Solid Security and try again.', 'better-wp-security' ), $id, $name ) ) );

			return false;
		}

		if ( $trim_value && is_string( $this->settings[ $var ] ) ) {
			$this->settings[ $var ] = trim( $this->settings[ $var ] );
		}

		$error = false;

		if ( 'string' === $type ) {
			$this->settings[ $var ] = (string) $this->settings[ $var ];
		} elseif ( 'non-empty-string' === $type ) {
			$this->settings[ $var ] = (string) $this->settings[ $var ];

			if ( empty( $this->settings[ $var ] ) ) {
				$error = sprintf( __( 'The %1$s value cannot be empty.', 'better-wp-security' ), $name );
			}
		} elseif ( 'title' === $type ) {
			$this->settings[ $var ] = sanitize_title( $this->settings[ $var ] );
		} elseif ( 'non-empty-title' === $type ) {
			$this->settings[ $var ] = sanitize_title( $this->settings[ $var ] );

			if ( empty( $this->settings[ $var ] ) ) {
				$error = sprintf( __( 'The %1$s value cannot be empty.', 'better-wp-security' ), $name );
			}
		} elseif ( 'text' === $type || 'non-empty-text' === $type ) {
			$string = (string) $this->settings[ $var ];
			$string = wp_strip_all_tags( $string );

			if ( $trim_value ) {
				$string = trim( $string );
			}

			$this->settings[ $var ] = $string;

			if ( 'non-empty-text' === $type && empty( $this->settings[ $var ] ) ) {
				$error = sprintf( __( 'The %1$s value cannot be empty.', 'better-wp-security' ), $name );
			}
		} elseif ( 'array' === $type ) {
			if ( ! is_array( $this->settings[ $var ] ) ) {
				if ( empty( $this->settings[ $var ] ) ) {
					$this->settings[ $var ] = array();
				} else {
					$this->settings[ $var ] = array( $this->settings[ $var ] );
				}
			}
		} elseif ( 'bool' === $type ) {
			if ( 'false' === $this->settings[ $var ] ) {
				$this->settings[ $var ] = false;
			} elseif ( 'true' === $this->settings[ $var ] ) {
				$this->settings[ $var ] = true;
			} else {
				$this->settings[ $var ] = (bool) $this->settings[ $var ];
			}
		} elseif ( 'int' === $type ) {
			$test_val = intval( $this->settings[ $var ] );
			if ( (string) $test_val === (string) $this->settings[ $var ] ) {
				$this->settings[ $var ] = $test_val;
			} else {
				$error = sprintf( __( 'The %1$s value must be an integer.', 'better-wp-security' ), $name );
			}
		} elseif ( 'positive-int' === $type ) {
			$test_val = intval( $this->settings[ $var ] );
			if ( (string) $test_val === (string) $this->settings[ $var ] && $test_val >= 0 ) {
				$this->settings[ $var ] = $test_val;
			} else {
				$error = sprintf( __( 'The %1$s value must be a positive integer.', 'better-wp-security' ), $name );
			}
		} elseif ( 'number' === $type ) {
			if ( is_numeric( $this->settings[ $var ] ) ) {
				$this->settings[ $var ] = (float) $this->settings[ $var ];
			} else {
				$error = sprintf( __( 'The %1$s value must be a number.', 'better-wp-security' ), $name );
			}
		} elseif ( 'email' === $type ) {
			$this->settings[ $var ] = sanitize_text_field( $this->settings[ $var ] );

			if ( empty( $this->settings[ $var ] ) || ! is_email( $this->settings[ $var ] ) ) {
				$error = sprintf( __( 'The %1$s value must be a valid email address.', 'better-wp-security' ), $name );
			}
		} elseif ( 'valid-username' === $type ) {
			$this->settings[ $var ] = sanitize_text_field( $this->settings[ $var ] );

			if ( ! empty( $this->settings[ $var ] ) && ! validate_username( $this->settings[ $var ] ) ) {
				$error = sprintf( __( 'The %1$s value is not a valid username.', 'better-wp-security' ), $name );
			}
		} elseif ( 'date' === $type ) {
			$val = $this->settings[ $var ];

			$separator = '[\-/\. ]';

			if ( preg_match( "|^(\d\d\d\d)$separator(\d\d?)$separator(\d\d?)$|", $val, $match ) ) {
				$year  = intval( $match[1] );
				$month = intval( $match[2] );
				$day   = intval( $match[3] );

				if ( ! checkdate( $month, $day, $year ) ) {
					$error = sprintf( __( 'The %1$s value must be a valid date.', 'better-wp-security' ), $name );
				}
			} else {
				$error = sprintf( __( 'The %1$s value must be a valid date in the format of YYYY-MM-DD.', 'better-wp-security' ), $name );
			}
		} elseif ( 'writable-directory' === $type ) {
			if ( ! is_string( $this->settings[ $var ] ) ) {
				$error = sprintf( __( 'The %1$s value must be a string.', 'better-wp-security' ), $name );
			} else {
				require_once( ITSEC_Core::get_core_dir() . 'lib/class-itsec-lib-directory.php' );

				$this->settings[ $var ] = rtrim( $this->settings[ $var ], DIRECTORY_SEPARATOR );

				if ( ! ITSEC_Lib_Directory::is_dir( $this->settings[ $var ] ) ) {
					$result = ITSEC_Lib_Directory::create( $this->settings[ $var ] );

					if ( is_wp_error( $result ) ) {
						$error = sprintf( _x( 'The directory supplied in %1$s cannot be used as a valid directory. %2$s', '%1$s is the input name. %2$s is the error message.', 'better-wp-security' ), $name, $result->get_error_message() );
					}
				}

				if ( empty( $error ) && ! ITSEC_Lib_Directory::is_writable( $this->settings[ $var ] ) ) {
					$error = sprintf( __( 'The directory supplied in %1$s is not writable. Please select a directory that can be written to.', 'better-wp-security' ), $name );
				}

				if ( empty( $error ) ) {
					ITSEC_Lib_Directory::add_file_listing_protection( $this->settings[ $var ] );
				}
			}
		} elseif ( 'writable-file' === $type ) {
			if ( ! is_string( $this->settings[ $var ] ) ) {
				$error = sprintf( __( 'The %1$s value must be a string.', 'better-wp-security' ), $name );
			} else {
				require_once( ITSEC_Core::get_core_dir() . 'lib/class-itsec-lib-directory.php' );

				if ( ! ITSEC_Lib_File::is_file( $this->settings[ $var ] ) && ITSEC_Lib_File::exists( $this->settings[ $var ] ) ) {
					$error = sprintf( __( 'The file path supplied in %1$s cannot be used as it already exists but is not a file. Please supply a valid file path.', 'better-wp-security' ), $name );
				} else {
					$result = ITSEC_Lib_Directory::create( dirname( $this->settings[ $var ] ) );

					if ( is_wp_error( $result ) ) {
						$error = sprintf( _x( 'The file path supplied in %1$s cannot be used as the parent directory cannot be created. %2$s', '%1$s is the input name. %2$s is the error message.', 'better-wp-security' ), $name, $result->get_error_message() );
					} elseif ( ! ITSEC_Lib_File::exists( $this->settings[ $var ] ) ) {
						$result = ITSEC_Lib_File::write( $this->settings[ $var ], '' );

						if ( is_wp_error( $result ) ) {
							$error = sprintf( __( 'The file path supplied in %1$s could not be created. Please supply a file path that can be written to.', 'better-wp-security' ), $name );
						} elseif ( ! is_writable( $this->settings[ $var ] ) ) {
							$error = sprintf( __( 'The file path supplied in %1$s was successfully created, but it cannot be updated. Please supply a file path that can be written to.', 'better-wp-security' ), $name );
						}
					} elseif ( ! is_writable( $this->settings[ $var ] ) ) {
						$error = sprintf( __( 'The file path supplied in %1$s is not writable. Please supply a file path that can be written to.', 'better-wp-security' ), $name );
					}
				}
			}
		} elseif ( is_array( $type ) && 2 === count( $type ) && $this === $type[0] ) {
			$this->settings[ $var ] = $this->convert_string_to_array( $this->settings[ $var ] );

			if ( ! is_array( $this->settings[ $var ] ) ) {
				$error = sprintf( __( 'The %1$s value must be a string with each entry separated by a new line.', 'better-wp-security' ), $name );
			} else {
				$invalid_entries = array();

				foreach ( $this->settings[ $var ] as $index => $entry ) {
					$entry                            = sanitize_text_field( trim( $entry ) );
					$this->settings[ $var ][ $index ] = $entry;

					if ( empty( $entry ) ) {
						unset( $this->settings[ $var ][ $index ] );
					} else {
						$result = call_user_func( $type, $entry );

						if ( false === $result ) {
							$invalid_entries[] = $entry;
						} else {
							$this->settings[ $var ][ $index ] = $result;
						}
					}
				}

				$this->settings[ $var ] = array_unique( $this->settings[ $var ] );

				if ( ! empty( $invalid_entries ) ) {
					$error = wp_sprintf( _n( 'The following entry in %1$s is invalid: %2$l', 'The following entries in %1$s are invalid: %2$l', count( $invalid_entries ), 'better-wp-security' ), $name, $invalid_entries );
				}
			}
		} elseif ( is_array( $type ) ) {
			if ( is_array( $this->settings[ $var ] ) ) {
				$invalid_entries = array();

				foreach ( $this->settings[ $var ] as $index => $entry ) {
					$entry                            = sanitize_text_field( trim( $entry ) );
					$this->settings[ $var ][ $index ] = $entry;

					if ( empty( $entry ) ) {
						unset( $this->settings[ $var ][ $index ] );
					} elseif ( ! in_array( $entry, $type, true ) ) {
						$invalid_entries[] = $entry;
					}
				}

				$this->settings[ $var ] = array_unique( $this->settings[ $var ] );

				if ( ! empty( $invalid_entries ) ) {
					$error = wp_sprintf( _n( 'The following entry in %1$s is invalid: %2$l', 'The following entries in %1$s are invalid: %2$l', count( $invalid_entries ), 'better-wp-security' ), $name, $invalid_entries );
					$type  = 'array';
				}
			} elseif ( ! in_array( $this->settings[ $var ], $type, true ) ) {
				$error = wp_sprintf( _n( 'The valid value for %1$s is: %2$l.', 'The valid values for %1$s are: %2$l.', count( $type ), 'better-wp-security' ), $name, $type );
				$type  = 'array';
			}
		} elseif ( 'canonical-roles' === $type ) {
			$roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );

			if ( is_array( $this->settings[ $var ] ) ) {
				$invalid_entries = array();

				foreach ( $this->settings[ $var ] as $index => $entry ) {
					$entry                            = sanitize_text_field( trim( $entry ) );
					$this->settings[ $var ][ $index ] = $entry;

					if ( empty( $entry ) ) {
						unset( $this->settings[ $var ][ $index ] );
					} elseif ( ! in_array( $entry, $roles, true ) ) {
						$invalid_entries[] = $entry;
					}
				}

				$this->settings[ $var ] = array_unique( $this->settings[ $var ] );

				if ( ! empty( $invalid_entries ) ) {
					$error = wp_sprintf( _n( 'The following entry in %1$s is invalid: %2$l', 'The following entries in %1$s are invalid: %2$l', count( $invalid_entries ), 'better-wp-security' ), $name, $invalid_entries );
				}
			} elseif ( ! in_array( $this->settings[ $var ], $roles, true ) ) {
				$error = wp_sprintf( _n( 'The valid value for %1$s is: %2$l.', 'The valid values for %1$s are: %2$l.', count( $roles ), 'better-wp-security' ), $name, $roles );
				$type  = 'array';
			}
		} elseif ( 'newline-separated-array' === $type ) {
			$this->settings[ $var ] = $this->convert_string_to_array( $this->settings[ $var ] );

			if ( ! is_array( $this->settings[ $var ] ) ) {
				$error = sprintf( __( 'The %1$s value must be a string with each entry separated by a new line.', 'better-wp-security' ), $name );
			}
		} elseif ( 'newline-separated-emails' === $type ) {
			$this->settings[ $var ] = $this->convert_string_to_array( $this->settings[ $var ] );

			if ( ! is_array( $this->settings[ $var ] ) ) {
				$error = sprintf( __( 'The %1$s value must be a string with each entry separated by a new line.', 'better-wp-security' ), $name );
			} else {
				$invalid_emails = array();

				foreach ( $this->settings[ $var ] as $index => $email ) {
					$email                            = sanitize_text_field( trim( $email ) );
					$this->settings[ $var ][ $index ] = $email;

					if ( empty( $email ) ) {
						unset( $this->settings[ $var ][ $index ] );
					} elseif ( ! is_email( $email ) ) {
						$invalid_emails[] = $email;
					}
				}

				$this->settings[ $var ] = array_unique( $this->settings[ $var ] );

				if ( ! empty( $invalid_emails ) ) {
					$error = wp_sprintf( _n( 'The following email in %1$s is invalid: %2$l', 'The following emails in %1$s are invalid: %2$l', count( $invalid_emails ), 'better-wp-security' ), $name, $invalid_emails );
				}
			}
		} elseif ( 'newline-separated-ips' === $type ) {
			$this->settings[ $var ] = $this->convert_string_to_array( $this->settings[ $var ] );

			if ( ! is_array( $this->settings[ $var ] ) ) {
				$error = sprintf( __( 'The %1$s value must be a string with each entry separated by a new line.', 'better-wp-security' ), $name );
			} else {
				require_once( ITSEC_Core::get_core_dir() . 'lib/class-itsec-lib-ip-tools.php' );

				$invalid_ips = array();

				foreach ( $this->settings[ $var ] as $index => $ip ) {
					$ip = trim( $ip );

					if ( '' === $ip ) {
						unset( $this->settings[ $var ][ $index ] );
					} else {
						$validated_ip = ITSEC_Lib_IP_Tools::ip_wild_to_ip_cidr( $ip );

						if ( false === $validated_ip ) {
							$invalid_ips[] = $ip;
						} else {
							$this->settings[ $var ][ $index ] = $validated_ip;
						}
					}
				}

				$this->settings[ $var ] = array_unique( $this->settings[ $var ] );

				if ( ! empty( $invalid_ips ) ) {
					$error = wp_sprintf( _n( 'The following IP in %1$s is invalid: %2$l', 'The following IPs in %1$s are invalid: %2$l', count( $invalid_ips ), 'better-wp-security' ), $name, $invalid_ips );
				}
			}
		} elseif ( 'user-groups' === $type ) {
			$source = ITSEC_Modules::get_container()->get( Matchables_Source::class );

			$this->sanitize_setting( 'array', $var, $name, $prevent_save_on_error, $trim_value, $custom_error );
			$invalid_user_groups = [];

			foreach ( $this->settings[ $var ] as $i => $group ) {
				if ( ! is_string( $group ) ) {
					unset( $this->settings[ $var ][ $i ] );

					continue;
				}

				if ( $source->has( $group ) ) {
					continue;
				}

				if ( in_array( $group, $this->previous_settings[ $var ], true ) ) {
					unset( $this->settings[ $var ][ $i ] );
				} else {
					$invalid_user_groups[] = $group;
				}
			}

			$this->settings[ $var ] = wp_is_numeric_array( $this->settings[ $var ] ) ? array_values( $this->settings[ $var ] ) : $this->settings[ $var ];

			if ( $invalid_user_groups ) {
				$error = wp_sprintf( _n( 'The following entry in %1$s is invalid: %2$l', 'The following entries in %1$s are invalid: %2$l', count( $invalid_user_groups ), 'better-wp-security' ), $name, $invalid_user_groups );
			}
		} elseif ( 'user-group' === $type ) {
			$source = ITSEC_Modules::get_container()->get( Matchables_Source::class );

			if ( ! $source->has( $this->settings[ $var ] ) ) {
				$error = sprintf( __( 'The user group selected for %1$s is invalid.', 'better-wp-security' ), $name );
			}
		} elseif ( 'newline-separated-extensions' === $type ) {
			$this->settings[ $var ] = $this->convert_string_to_array( $this->settings[ $var ] );

			if ( ! is_array( $this->settings[ $var ] ) ) {
				$error = sprintf( __( 'The %1$s value must be a string with each entry separated by a new line.', 'better-wp-security' ), $name );
			} else {
				$invalid_extensions = array();

				foreach ( $this->settings[ $var ] as $index => $extension ) {
					if ( ! preg_match( '/^(\.[^.]+)+$/', $extension ) ) {
						$invalid_extensions[] = $extension;
					}
				}

				$this->settings[ $var ] = array_unique( $this->settings[ $var ] );

				if ( ! empty( $invalid_extensions ) ) {
					$error = wp_sprintf( _n( 'The following extension in %1$s is invalid: %2$l', 'The following extensions in %1$s are invalid: %2$l', count( $invalid_extensions ), 'better-wp-security' ), $name, $invalid_extensions );
				}
			}
		} elseif ( is_string( $type ) && 0 === strpos( $type, 'cb-items:' ) ) {

			list( , $method ) = explode( ':', $type );

			if ( ! is_array( $this->settings[ $var ] ) ) {
				$error = sprintf( __( 'The %1$s value must be an array.', 'better-wp-security' ), $name );
			} else {
				$invalid_entries = array();

				foreach ( $this->settings[ $var ] as $index => $entry ) {

					if ( empty( $entry ) ) {
						unset( $this->settings[ $var ][ $index ] );
					} else {
						$result = $this->{$method}( $entry, $index );

						if ( false === $result ) {
							$invalid_entries[] = is_string( $entry ) ? $entry : $index;
						} elseif ( is_wp_error( $result ) ) {
							$invalid_entries[] = "'{$index}': {$result->get_error_message()}";
						} else {
							$this->settings[ $var ][ $index ] = $result;
						}
					}
				}

				if ( ! empty( $invalid_entries ) ) {
					$error = wp_sprintf( _n( 'The following entry in %1$s is invalid: %2$l', 'The following entries in %1$s are invalid: %2$l', count( $invalid_entries ), 'better-wp-security' ), $name, $invalid_entries );
				}
			}

		} else {
			/* translators: 1: sanitize type, 2: input name */
			$error = sprintf( __( 'An invalid sanitize type of "%1$s" was received for the %2$s input.', 'better-wp-security' ), $type, $name );
		}

		if ( false !== $error ) {
			if ( $custom_error ) {
				$error = $custom_error;
			}

			$this->add_error( $this->generate_error( $id, $var, $type, $error ) );
			$this->vars_to_skip_validate_matching_types[] = $var;

			if ( $prevent_save_on_error && ITSEC_Core::is_interactive() ) {
				$this->set_can_save( false );
			}

			return false;
		}

		return true;
	}

	/**
	 * Validates a user groups setting.
	 *
	 * @param string $name The setting label.
	 * @param string $var  The setting var.
	 *
	 * @return array|WP_Error The sanitized user groups, or a WP_Error.
	 */
	protected function validate_user_groups( $name, $var ) {
		$new_value      = ITSEC_Lib::array_get( $this->settings, $var );
		$previous_value = ITSEC_Lib::array_get( $this->previous_settings, $var );

		$source              = ITSEC_Modules::get_container()->get( Matchables_Source::class );
		$invalid_user_groups = [];

		foreach ( $new_value as $i => $group ) {
			if ( ! is_string( $group ) ) {
				unset( $new_value[ $i ] );

				continue;
			}

			if ( $source->has( $group ) ) {
				continue;
			}

			if ( in_array( $group, $previous_value, true ) ) {
				unset( $new_value[ $i ] );
			} else {
				$invalid_user_groups[] = $group;
			}
		}

		$new_value = wp_is_numeric_array( $new_value ) ? array_values( $new_value ) : $new_value;

		if ( ! $invalid_user_groups ) {
			return $new_value;
		}

		$error = wp_sprintf(
			_n( 'The following entry in %1$s is invalid: %2$l', 'The following entries in %1$s are invalid: %2$l', count( $invalid_user_groups ), 'better-wp-security' ),
			$name,
			$invalid_user_groups
		);

		return $this->generate_error( $this->get_id(), $var, 'user-groups', $error );
	}

	protected function generate_error( $id, $var, $type, $error ) {
		return new WP_Error( "itsec-validator-$id-invalid-type-$var-$type", $error );
	}

	final protected function convert_string_to_array( $string ) {
		if ( is_string( $string ) ) {
			$array = preg_split( "/[\r\n]+/", $string );
		} elseif ( is_array( $string ) ) {
			$array = $string;
		} else {
			return $string;
		}

		foreach ( $array as $key => $val ) {
			$val = trim( $val );

			if ( empty( $val ) ) {
				unset( $array[ $key ] );
			} else {
				$array[ $key ] = $val;
			}
		}

		return $array;
	}

	/**
	 * Adds an error to be displayed to the user.
	 *
	 * @param \WP_Error $error
	 */
	final protected function add_error( $error ) {
		$this->errors[] = $error;
	}

	final public function found_errors() {
		return ! empty( $this->errors );
	}

	final public function get_errors() {
		return $this->errors;
	}

	final protected function add_message( $message ) {
		$this->messages[] = $message;
	}

	final public function get_messages() {
		return $this->messages;
	}

	final protected function set_can_save( $can_save ) {
		$this->can_save = (bool) $can_save;
	}

	final public function can_save() {
		return $this->can_save;
	}

	final protected function set_needs_refresh( $needs_refresh ) {
		$this->needs_refresh = (bool) $needs_refresh;
	}

	final public function needs_refresh() {
		return $this->needs_refresh;
	}

	final public function get_settings() {
		return $this->settings;
	}

	public function __get( $name ) {
		if ( 'defaults' === $name ) {
			_deprecated_function( static::class . '::$defaults', '7.0.0', 'ITSEC_Modules::get_default' );

			return $this->settings_obj->get_defaults();
		}

		trigger_error( sprintf( 'Undefined property: %s::$%s', static::class, $name ) );

		return null;
	}

	public function __isset( $name ) {
		return 'defaults' === $name;
	}
}
