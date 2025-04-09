<?php

namespace iThemesSecurity\Ban_Hosts;

final class Deprecated_Filter_Source implements Source {

	/** @var Source */
	private $source;

	/**
	 * Deprecated_Filter_Source constructor.
	 *
	 * @param Source $source
	 */
	public function __construct( Source $source ) { $this->source = $source; }

	public function find_ban_for_host( $host ) {
		$ban = $this->source->find_ban_for_host( $host );

		if ( ! has_filter( 'itsec_filter_blacklisted_ips' ) ) {
			return $ban;
		}

		if ( $ban ) {
			$hosts = [ $host ];
		} else {
			$hosts = [];
		}

		$hosts = apply_filters_deprecated( 'itsec_filter_blacklisted_ips', [ $hosts ], '6.7.0' );

		if ( ! $hosts ) {
			return null;
		}

		foreach ( $hosts as $maybe_host ) {
			if ( \ITSEC_Lib_IP_Tools::intersect( $host, $maybe_host ) ) {
				return $ban ?: new Legacy_Ban( $maybe_host );
			}
		}

		return null;
	}
}
