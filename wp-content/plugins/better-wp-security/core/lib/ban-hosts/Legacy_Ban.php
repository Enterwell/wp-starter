<?php

namespace iThemesSecurity\Ban_Hosts;

final class Legacy_Ban implements Ban {

	/** @var string */
	private $host;

	/**
	 * Legacy_Ban constructor.
	 *
	 * @param string $host
	 */
	public function __construct( $host ) { $this->host = $host; }

	public function matches( $ip ) {
		return \ITSEC_Lib_IP_Tools::intersect( $ip, $this->host );
	}

	public function get_created_at() {
		return null;
	}

	public function get_created_by() {
		return null;
	}

	public function get_comment() {
		return __( 'Dynamic ban added with the deprecated itsec_filter_blacklisted_ips filter.', 'better-wp-security' );
	}

	public function __toString() {
		return $this->host;
	}
}
