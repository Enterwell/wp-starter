<?php

final class ITSEC_Mutex {
	/** @var int */
	private $id;

	/** @var string */
	private $name;

	/** @var int */
	private $expires;

	/**
	 * ITSEC_Mutex constructor.
	 *
	 * @param int    $id
	 * @param string $name
	 * @param int    $expires
	 */
	private function __construct( $id, $name, $expires ) {
		$this->id      = $id;
		$this->name    = $name;
		$this->expires = $expires;
	}

	/**
	 * Get a mutex.
	 *
	 * @param string $name Name of the mutex.
	 * @param int    $ttl  How long to hold the mutex for.
	 *
	 * @return ITSEC_Mutex|null Returns null if the mutex is already being held.
	 */
	public static function get( $name, $ttl = 30 ) {
		global $wpdb;

		$expires_at = time() + $ttl;

		while ( ! $id = self::create( $name, $expires_at ) ) {
			$existing = $wpdb->get_row( $wpdb->prepare(
				"SELECT `mutex_id`, `mutex_expires` FROM `{$wpdb->base_prefix}itsec_mutexes` WHERE `mutex_name` = %s",
				$name
			), ARRAY_A );

			if ( ! $existing ) {
				continue;
			}

			if ( $existing['mutex_expires'] < time() ) {
				self::delete_by_id( $existing['mutex_id'] );
				continue;
			}

			return null;
		}

		return new self( $wpdb->insert_id, $name, $expires_at );
	}

	/**
	 * Release the mutex back.
	 */
	public function release() {
		self::delete_by_id( $this->get_id() );
	}

	/**
	 * Has the mutex expired.
	 *
	 * @return bool
	 */
	public function is_expired() {
		return $this->get_expires() < time();
	}

	/**
	 * Checks if this mutex still exists.
	 *
	 * @return bool
	 */
	public function exists() {
		global $wpdb;

		return (bool) $wpdb->get_var( $wpdb->prepare(
			"SELECT `mutex_id` FROM {$wpdb->base_prefix}itsec_mutexes WHERE `mutex_id` = %d",
			$this->get_id()
		) );
	}

	/**
	 * Get the mutex's id.
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get the name of the mutex.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the epoch the mutex expires at.
	 *
	 * @return int
	 */
	public function get_expires() {
		return $this->expires;
	}

	/**
	 * Delete a mutex by it's id.
	 *
	 * @param string $id
	 */
	private static function delete_by_id( $id ) {
		global $wpdb;

		$wpdb->delete( $wpdb->base_prefix . 'itsec_mutexes', [
			'mutex_id' => $id,
		] );
	}

	/**
	 * Create a mutex record.
	 *
	 * @param string $name
	 * @param int    $expires_at
	 *
	 * @return int The mutex id, or 0 if it could not be acquired.
	 */
	private static function create( $name, $expires_at ) {
		global $wpdb;

		$r = $wpdb->query( $wpdb->prepare(
			"INSERT IGNORE INTO `{$wpdb->base_prefix}itsec_mutexes` (`mutex_name`, `mutex_expires`) VALUES (%s, %s) /* LOCK */",
			$name,
			$expires_at
		) );

		if ( ! $r || ! $wpdb->insert_id ) {
			return 0;
		}

		return (int) $wpdb->insert_id;
	}
}
