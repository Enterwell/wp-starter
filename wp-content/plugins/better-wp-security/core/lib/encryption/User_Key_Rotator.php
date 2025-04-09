<?php

namespace iThemesSecurity\Encryption;

final class User_Key_Rotator implements \Countable {

	/** @var User_Encryption */
	private $old_encryption;

	/** @var User_Encryption */
	private $new_encryption;

	/** @var int */
	private $count = 0;

	public function __construct( User_Encryption $old_encryption, User_Encryption $new_encryption ) {
		$this->old_encryption = $old_encryption;
		$this->new_encryption = $new_encryption;
	}

	/**
	 * Decrypts and re-encrypts a value.
	 *
	 * @param string $original The original encrypted value.
	 * @param int    $user_id  The user id.
	 *
	 * @return string The newly encrypted value.
	 */
	public function __invoke( string $original, int $user_id ): string {
		$decrypted = $this->old_encryption->decrypt( $original, $user_id );
		$encrypted = $this->new_encryption->encrypt( $decrypted, $user_id );

		$this->count ++;

		return $encrypted;
	}

	public function count(): int {
		return $this->count;
	}
}
