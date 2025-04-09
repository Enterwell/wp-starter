<?php

namespace iThemesSecurity\Exception;

class WP_Error extends \Exception implements Exception {

	/** @var \WP_Error */
	private $error;

	public function __construct( \WP_Error $error, \Exception $previous = null ) {
		$this->error = $error;
		parent::__construct( wp_sprintf( '%l', $error->get_error_messages() ), 0, $previous );
	}

	/**
	 * Create a WP Error instance from an error code and message.
	 *
	 * @param string $code
	 * @param string $message
	 * @param array  $data
	 *
	 * @return static
	 */
	public static function from_code( $code, $message, array $data = [] ) {
		return new static( new \WP_Error( $code, $message, $data ) );
	}

	/**
	 * Get the WP Error instance.
	 *
	 * @return \WP_Error
	 */
	public function get_error() {
		return $this->error;
	}
}
