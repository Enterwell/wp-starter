<?php

/**
 * Class ITSEC_IP_Detector
 *
 * @internal Use {@see ITSEC_Lib::get_ip()} instead of this class directly.
 */
class ITSEC_IP_Detector {

	public const FROM_LEFT = 'left';
	public const FROM_RIGHT = 'right';

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
	 * @param string $from     Where the position is based from. Note, only use
	 *                         right-based indexes. Left-based indexes are not
	 *                         secure and only maintained for legacy compatibility.
	 *                         It will generate a warning in a future releases.
	 *
	 * @return $this
	 */
	public function add_header( $header, $position = - 1, $from = self::FROM_LEFT ) {
		$this->headers[] = [ $header, $position, $from ];

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
		foreach ( $this->headers as list( $header, $position, $from ) ) {
			$ip = $this->get_for_header( $header, $position, $from );

			if ( ! $ip ) {
				continue;
			}

			if ( $this->is_valid_ip( $ip ) ) {
				return $ip;
			}
		}

		return '';
	}

	/**
	 * Checks if the IP address is valid.
	 *
	 * Will accept a private IP only if we in the allow private IP loop.
	 *
	 * @param string $ip
	 *
	 * @return bool
	 */
	private function is_valid_ip( string $ip ): bool {
		$flags = 0;

		if ( ! $this->allow_private ) {
			$flags |= FILTER_FLAG_NO_RES_RANGE | FILTER_FLAG_NO_PRIV_RANGE;
		}

		return (bool) filter_var( $ip, FILTER_VALIDATE_IP, $flags );
	}

	/**
	 * Get the IP address for a header.
	 *
	 * @param string $header
	 * @param int    $position
	 * @param string $from
	 *
	 * @return string
	 */
	private function get_for_header( $header, $position, $from ) {
		if ( empty( $this->server[ $header ] ) ) {
			return '';
		}

		$value = trim( $this->server[ $header ] );

		if ( - 1 === $position ) {
			$ips = array_reverse( array_map( 'trim', explode( ',', $value ) ) );

			foreach ( $ips as $ip ) {
				if ( $this->is_valid_ip( $ip ) ) {
					return $ip;
				}
			}

			return '';
		}

		// Handle Forwarded: header syntax https://tools.ietf.org/html/rfc7239#section-4
		if ( preg_match_all( '{(?:for)=(?:"?\[?)([a-z0-9\.:_\-/]*)}i', $value, $matches, PREG_SET_ORDER ) ) {
			if ( $from === self::FROM_RIGHT ) {
				$matches = array_reverse( $matches );
			}

			if ( ! empty( $matches[ $position ][1] ) ) {
				return $matches[ $position ][1];
			}
		}

		$parts = preg_split( '/[, ]+/', $value );

		if ( $from === self::FROM_RIGHT ) {
			$parts = array_reverse( $parts );
		}

		if ( ! empty( $parts[ $position ] ) ) {
			return $parts[ $position ];
		}

		return '';
	}
}
