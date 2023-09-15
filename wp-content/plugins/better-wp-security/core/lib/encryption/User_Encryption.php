<?php

namespace iThemesSecurity\Encryption;

use RuntimeException;

interface User_Encryption {

	/**
	 * Checks if the given text is likely encrypted.
	 *
	 * @param string $message Text to check.
	 *
	 * @return bool
	 */
	public static function is_encrypted( string $message ): bool;

	/**
	 * Encrypts a secret.
	 *
	 * @param string $message Data to encrypt.
	 * @param int    $user_id User ID.
	 *
	 * @return string The encrypted text.
	 * @throws RuntimeException Encryption failed.
	 */
	public function encrypt( string $message, int $user_id ): string;

	/**
	 * Decrypts a secret.
	 *
	 * @param string $encrypted Encrypted secret.
	 * @param int    $user_id   User ID.
	 *
	 * @return string The clear text.
	 * @throws RuntimeException Decryption failed.
	 */
	public function decrypt( string $encrypted, int $user_id ): string;
}
