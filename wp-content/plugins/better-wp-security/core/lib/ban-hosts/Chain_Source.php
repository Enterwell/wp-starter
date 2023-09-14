<?php

namespace iThemesSecurity\Ban_Hosts;

final class Chain_Source implements Source {

	/** @var Source[] */
	private $sources;

	/**
	 * Chain_Source constructor.
	 *
	 * @param Source ...$sources List of sources to pull from.
	 */
	public function __construct( Source ...$sources ) {
		$this->sources = $sources;
	}

	public function find_ban_for_host( $host ) {
		foreach ( $this->sources as $source ) {
			if ( $ban = $source->find_ban_for_host( $host ) ) {
				return $ban;
			}
		}

		return null;
	}
}
