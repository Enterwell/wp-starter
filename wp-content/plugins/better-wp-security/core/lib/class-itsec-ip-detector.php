<?php

/**
 * Class ITSEC_IP_Detector
 *
 * @internal Use {@see ITSEC_Lib::get_ip()} instead of this class directly.
 */
class ITSEC_IP_Detector {

	/** @var array */
	private $server;

	/** @var array */
	private $headers = [];

	/** @var bool */
	private $allow_private = false;

	/**
	 * ITSEC_IP_Detector constructor.
	 *
	 * A new detector instance should be created whenever you look for a new IP.
	 *
	 * @param array $server A copy of $_SERVER.
	 */
	public function __construct( array $server ) { $this->server = $server; }

	/**
	 * Add a header to check for an IP.
	 *
	 * @param string $header   The header name.
	 * @param int    $position If multiple IPs are included in this header,
	 *                         the 0-based position of the IP to return.
	 *
	 * @return $this
	 */
	public function add_header( $header, $position = - 1 ) {
		$this->headers[] = [ $header, $position ];

		return $this;
	}

	/**
	 * Get the IP address for this request.
	 *
	 * @return string
	 */
	public function get() {
		if ( $ip = $this->get_ip() ) {
			return $ip;
		}

		$this->allow_private = true;

		return $this->get_ip();
	}

	/**
	 * Get the IP given the current configuration.
	 *
	 * @return string
	 */
	private function get_ip() {
		foreach ( $this->headers as list( $header, $position ) ) {
			$ip = $this->get_for_header( $header, $position );

			if ( ! $ip ) {
				continue;
			}

			if ( $this->allow_private ) {
				$ip = filter_var( $ip, FILTER_VALIDATE_IP );
			} else {
				$ip = filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE | FILTER_FLAG_NO_PRIV_RANGE );
			}

			if ( $ip ) {
				return (string) $ip;
			}
		}

		return '';
	}

	/**
	 * Get the IP address for a header.
	 *
	 * @param string $header
	 * @param int    $position
	 *
	 * @return string
	 */
	private function get_for_header( $header, $position ) {
		if ( empty( $this->server[ $header ] ) ) {
			return '';
		}

		$value = trim( $this->server[ $header ] );

		if ( - 1 === $position ) {
			return explode( ',', $value )[0];
		}

		// Handle Forwarded: header syntax https://tools.ietf.org/html/rfc7239#section-4
		if ( preg_match_all( '{(?:for)=(?:"?\[?)([a-z0-9\.:_\-/]*)}i', $value, $matches, PREG_SET_ORDER ) ) {
			if ( ! empty( $matches[ $position ][1] ) ) {
				return $matches[ $position ][1];
			}
		}

		$parts = preg_split( '/[, ]/', $value );

		if ( ! empty( $parts[ $position ] ) ) {
			return $parts[ $position ];
		}

		return '';
	}
}
