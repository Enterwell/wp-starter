<?php

namespace iThemesSecurity\Encryption;

use Exception;
use RuntimeException;
use SodiumException;

final class User_Encryption_Sodium implements User_Encryption {
	/**
	 * Prefix for encrypted secrets. Contains a version identifier.
	 *
	 * $t1$ -> v1 (RFC 6238, encrypted with XChaCha20-Poly1305, with a key derived from HMAC-SHA256
	 *                  of SECURE_AUTH_SAL.)
	 *
	 * @var string
	 */
	private const ENCRYPTED_PREFIX = '$t1$';

	/**
	 * Current "version" of the encryption protocol.
	 *
	 * 1 -> $t1$nonce|ciphertext|tag
	 */
	private const ENCRYPTED_VERSION = 1;

	/** @var string */
	private $secret;

	/**
	 * Instantiates a new User Encryption implementation.
	 *
	 * @param string $secret Random bytes.
	 */
	public function __construct( string $secret ) {
		$this->secret = $secret;

		if (
			! function_exists( 'sodium_crypto_aead_xchacha20poly1305_ietf_encrypt' ) &&
			! function_exists( 'sodiumCompatAutoloader' )
		) {
			require_once ABSPATH . WPINC . '/sodium_compat/autoload.php';
		}
	}

	public static function is_encrypted( string $message ): bool {
		if ( strlen( $message ) < 40 ) {
			return false;
		}

		if ( strpos( $message, self::ENCRYPTED_PREFIX ) !== 0 ) {
			return false;
		}

		return true;
	}

	public function encrypt( string $message, int $user_id ): string {
		$prefix = $this->get_version_header();

		try {
			$nonce      = random_bytes( 24 );
			$ciphertext = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
				$message,
				$this->serialize_aad( $prefix, $nonce, $user_id ),
				$nonce,
				$this->get_encryption_key()
			);
		} catch ( SodiumException $e ) {
			throw new RuntimeException( 'Encryption failed', 0, $e );
		} catch ( Exception $e ) {
			throw new RuntimeException( 'Nonce generation failed.', 0, $e );
		}

		return self::ENCRYPTED_PREFIX . base64_encode( $nonce . $ciphertext );
	}

	public function decrypt( string $encrypted, int $user_id ): string {
		if ( strlen( $encrypted ) < 4 ) {
			throw new RuntimeException( 'Message is too short to be encrypted' );
		}

		$prefix  = substr( $encrypted, 0, 4 );
		$version = self::get_version_id( $prefix );

		if ( $version !== 1 ) {
			throw new RuntimeException( 'Unknown version: ' . $version );
		}

		$decoded    = base64_decode( substr( $encrypted, 4 ) );
		$nonce      = substr( $decoded, 0, 24 );
		$ciphertext = substr( $decoded, 24 );
		try {
			$decrypted = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
				$ciphertext,
				self::serialize_aad( $prefix, $nonce, $user_id ),
				$nonce,
				$this->get_encryption_key( $version )
			);
		} catch ( SodiumException $ex ) {
			throw new RuntimeException( 'Decryption failed', 0, $ex );
		}

		// If we don't have a string, throw an exception because decryption failed.
		if ( ! is_string( $decrypted ) ) {
			throw new RuntimeException( 'Could not decrypt secret' );
		}

		return $decrypted;
	}

	/**
	 * Serializes the Additional Authenticated Data for secret encryption.
	 *
	 * @param string $prefix  Version prefix.
	 * @param string $nonce   Encryption nonce.
	 * @param int    $user_id User ID.
	 *
	 * @return string
	 */
	private function serialize_aad( string $prefix, string $nonce, int $user_id ): string {
		return $prefix . $nonce . pack( 'N', $user_id );
	}

	/**
	 * Gets the version prefix from a given version number.
	 *
	 * @param int $number Version number.
	 *
	 * @return string
	 * @throws RuntimeException For incorrect versions.
	 */
	private function get_version_header( int $number = self::ENCRYPTED_VERSION ): string {
		switch ( $number ) {
			case 1:
				return '$t1$';
			default:
				throw new RuntimeException( 'Incorrect version number: ' . $number );
		}
	}

	/**
	 * Gets the version prefix from a given version number.
	 *
	 * @param string $prefix Version prefix.
	 *
	 * @return int
	 * @throws RuntimeException For incorrect versions.
	 */
	private function get_version_id( string $prefix = self::ENCRYPTED_PREFIX ): int {
		switch ( $prefix ) {
			case '$t1$':
				return 1;
			default:
				throw new RuntimeException( 'Incorrect version identifier: ' . $prefix );
		}
	}

	/**
	 * Gets the encryption key for encrypting secrets.
	 *
	 * @param int $version Key derivation strategy.
	 *
	 * @return string
	 * @throws RuntimeException For incorrect versions.
	 */
	private function get_encryption_key( int $version = self::ENCRYPTED_VERSION ): string {
		switch ( $version ) {
			case 1:
				return hash_hmac( 'sha256', $this->secret, 'itsec-user-encryption', true );
			default:
				throw new RuntimeException( 'Incorrect version number: ' . $version );
		}
	}
}
