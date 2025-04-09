<?php

use iThemesSecurity\Encryption\User_Encryption;
use iThemesSecurity\Encryption\User_Encryption_Sodium;
use iThemesSecurity\Encryption\User_Key_Rotator;
use iThemesSecurity\Lib\Result;

class ITSEC_Lib_Encryption {

	/**
	 * PHP Constant that the secret used to derive the key is stored in.
	 * For instance, define( 'ITSEC_ENCRYPTION_KEY', 'randombyteshere' );
	 */
	public const SECRET_NAME = 'ITSEC_ENCRYPTION_KEY';

	private const TEST_STRING = 'test-string';
	private const TEST_STRING_OPTION = 'itsec_user_encryption_test';

	/** @var User_Encryption */
	private static $user_encryption;

	/**
	 * Checks if Encryption is available.
	 *
	 * @return bool
	 */
	public static function is_available(): bool {
		return defined( self::SECRET_NAME ) && strlen( constant( self::SECRET_NAME ) ) > 16;
	}

	/**
	 * Generates a secret key to use for encryption.
	 *
	 * @return string
	 *
	 * @throws RuntimeException If a random secret cannot be generated.
	 */
	public static function generate_secret(): string {
		$secret = wp_generate_password( 64, true, true );

		if ( ! is_string( $secret ) || strlen( $secret ) !== 64 ) {
			throw new RuntimeException( 'Could not generate secret key.' );
		}

		return base64_encode( $secret );
	}

	/**
	 * Saves a secret key to `wp-config.php`.
	 *
	 * @param string $secret The secret key to save.
	 *
	 * @return Result
	 */
	public static function save_secret_key( string $secret ): Result {
		if ( ! ITSEC_Files::can_write_to_files() ) {
			return Result::error( new WP_Error(
				'itsec.encryption.cannot-write-to-files',
				__( 'Solid Security does not have permission to write to your wp-config.php file.', 'better-wp-security' )
			) );
		}

		if ( ! defined( self::SECRET_NAME ) ) {
			$php = sprintf(
				"define( '%s', '%s' );%s",
				ITSEC_Lib_Encryption::SECRET_NAME,
				$secret,
				PHP_EOL
			);

			$written = ITSEC_Lib_Config_File::append_wp_config( $php, true );

			if ( is_wp_error( $written ) ) {
				return Result::error( $written );
			}

			self::set_test_string( $secret );

			return Result::success()
			             ->add_success_message( __( 'Added new encryption key to the wp-config.php file.', 'better-wp-security' ) );
		}

		$file     = ITSEC_Lib_Config_File::get_wp_config_file_path();
		$contents = ITSEC_Lib_File::read( $file );

		if ( is_wp_error( $contents ) ) {
			return Result::error( $contents );
		}

		if ( ! $contents ) {
			return Result::error( new WP_Error(
				'itsec.encryption.empty-wp-config-file',
				__( 'Encountered an empty wp-config.php file.', 'better-wp-security' )
			) );
		}

		$current_secret = constant( self::SECRET_NAME );
		$matches_found  = $current_secret ? substr_count( $contents, $current_secret ) : 0;

		if ( ! $current_secret || ! $matches_found ) {
			return Result::error( new WP_Error(
				'itsec.encryption.secret-not-found',
				__( 'Cannot find existing encryption secret in wp-config.php. Instead, manually update your site encryption key.', 'better-wp-security' )
			) );
		}

		if ( $matches_found > 1 ) {
			return Result::error( new WP_Error(
				'itsec.encryption.multiple-secrets-found',
				__( 'The previous encryption key was defined multiple times in wp-config.php. Instead, manually update your site encryption key.', 'better-wp-security' )
			) );
		}

		$replaced = str_replace( $current_secret, $secret, $contents );

		if ( ! $replaced ) {
			return Result::error( new WP_Error(
				'itsec.encryption.replace-failed',
				__( 'Could not replace the existing encryption key with a new key.', 'better-wp-security' )
			) );
		}

		$written = ITSEC_Lib_File::write( $file, $replaced );

		if ( is_wp_error( $written ) ) {
			return Result::error( $written );
		}

		if ( ! self::get_test_string() ) {
			self::set_test_string( $secret );
		}

		return Result::success()
		             ->add_success_message( __( 'Updated the encryption key in the wp-config.php file.', 'better-wp-security' ) );
	}

	/**
	 * Checks if the string is an encrypted secret.
	 *
	 * @param string $message Message to check.
	 *
	 * @return bool
	 */
	public static function is_encrypted( string $message ): bool {
		return User_Encryption_Sodium::is_encrypted( $message );
	}

	/**
	 * Encrypts a secret for a particular user.
	 *
	 * @param string $message The message to encrypt..
	 * @param int    $user_id User ID.
	 *
	 * @return string The encrypted text.
	 * @throws RuntimeException Encryption failed.
	 */
	public static function encrypt_for_user( string $message, int $user_id ): string {
		return self::user_encryption()->encrypt( $message, $user_id );
	}

	/**
	 * Decrypts a secret.
	 *
	 * Version information is encoded with the ciphertext and thus omitted from this function.
	 *
	 * @param string $encrypted Encrypted secret.
	 * @param int    $user_id   User ID.
	 *
	 * @return string The clear text.
	 * @throws RuntimeException Decryption failed.
	 */
	public static function decrypt_for_user( string $encrypted, int $user_id ): string {
		return self::user_encryption()->decrypt( $encrypted, $user_id );
	}

	/**
	 * Runs the key rotation process when you have access to the previous secret key.
	 *
	 * @param string $old_key The previous key.
	 *
	 * @return Result<User_Key_Rotator>
	 */
	public static function rotate_with_old_key( string $old_key ): Result {
		if ( ! self::validate_old_key( $old_key ) ) {
			return Result::error( new WP_Error(
				'itsec.encryption.rotate-old-key-invalid',
				__( 'The provided key is not valid.', 'better-wp-security' )
			) );
		}

		$old = new User_Encryption_Sodium( $old_key );
		$new = self::user_encryption();

		$rotator = new User_Key_Rotator( $old, $new );

		self::set_test_string();

		return self::do_rotation( $rotator );
	}

	/**
	 * Runs the key rotation process when you have access to the new secret key.
	 *
	 * @param string $new_key The new key.
	 *
	 * @return Result<User_Key_Rotator>
	 */
	public static function rotate_with_new_key( string $new_key ): Result {
		$old = self::user_encryption();
		$new = new User_Encryption_Sodium( $new_key );

		$rotator = new User_Key_Rotator( $old, $new );

		self::set_test_string( $new_key );

		return self::do_rotation( $rotator );
	}

	/**
	 * Runs the actual key rotation process.
	 *
	 * @param User_Key_Rotator $rotator
	 *
	 * @return Result<User_Key_Rotator>
	 */
	private static function do_rotation( User_Key_Rotator $rotator ): Result {
		$result = Result::success( $rotator );

		/**
		 * Fires when an encryption key is being rotated.
		 *
		 * @param User_Key_Rotator $rotator
		 * @param Result           $result
		 */
		do_action( 'itsec_encryption_rotate_user_keys', $rotator, $result );

		$result->add_success_message( __( 'Completed key rotation.', 'better-wp-security' ) );

		$result->add_info_message(
			sprintf(
				_n(
					'Rotated %d encrypted secret.',
					'Rotated %d encrypted secrets.',
					$rotator->count(),
					'better-wp-security'
				),
				$rotator->count()
			)
		);

		return $result;
	}

	/**
	 * Checks if the Encryption Key constant has changed.
	 *
	 * @return bool
	 */
	public static function has_encryption_key_changed(): bool {
		if ( ! self::is_available() ) {
			if ( self::get_test_string() ) {
				return true;
			}

			return false;
		}

		$stored = self::get_test_string();

		if ( ! $stored ) {
			self::set_test_string();

			return false;
		}

		try {
			$decrypted = self::decrypt_for_user( $stored, 0 );

			return $decrypted !== self::TEST_STRING;
		} catch ( RuntimeException $e ) {
			return true;
		}
	}

	/**
	 * Resets the encryption key has changed warning.
	 *
	 * @return void
	 */
	public static function reset_encryption_key_changed_warning(): void {
		if ( self::is_available() ) {
			self::set_test_string();
		} else {
			delete_site_option( self::TEST_STRING_OPTION );
		}
	}

	/**
	 * Validates that the given key can be used to decrypt previous secrets.
	 *
	 * @param string $secret_key Previous secret key.
	 *
	 * @return bool
	 */
	private static function validate_old_key( string $secret_key ): bool {
		$test_string = self::get_test_string();

		if ( ! $test_string ) {
			return true;
		}

		try {
			$encryption = new User_Encryption_Sodium( $secret_key );

			return $encryption->decrypt( $test_string, 0 ) === self::TEST_STRING;
		} catch ( RuntimeException $e ) {
			return false;
		}
	}

	/**
	 * Gets the test string used to detect if the encryption
	 * key has been manually changed by the user.
	 *
	 * @return string
	 */
	private static function get_test_string(): string {
		return get_site_option( self::TEST_STRING_OPTION );
	}

	/**
	 * Sets the test string used to detect if the encryption
	 * key has been manually changed by the user.
	 *
	 * @param string $secret The secret to use to encrypt the test string.
	 *
	 * @return void
	 */
	private static function set_test_string( string $secret = '' ): void {
		if ( $secret ) {
			$encryption = new User_Encryption_Sodium( $secret );
		} else {
			$encryption = self::user_encryption();
		}

		update_site_option( self::TEST_STRING_OPTION, $encryption->encrypt( self::TEST_STRING, 0 ) );
	}

	private static function user_encryption(): User_Encryption {
		if ( self::$user_encryption === null ) {
			if ( ! defined( self::SECRET_NAME ) ) {
				throw new RuntimeException( 'No secret key configured.' );
			}

			self::$user_encryption = new User_Encryption_Sodium( constant( self::SECRET_NAME ) );
		}

		return self::$user_encryption;
	}
}
