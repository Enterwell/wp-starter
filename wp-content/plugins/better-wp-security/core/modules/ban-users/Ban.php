<?php

namespace iThemesSecurity\Ban_Users;

use iThemesSecurity\Actor\Actor;
use iThemesSecurity\Ban_Hosts\Repository_Ban;

final class Ban implements Repository_Ban {

	/** @var int */
	private $id;

	/** @var string */
	private $host;

	/** @var \DateTimeImmutable|null */
	private $created_at;

	/** @var Actor|null */
	private $created_by;

	/** @var string */
	private $comment;

	/**
	 * Host constructor.
	 *
	 * @param string                  $host       The host, can be given in CIDR notation.
	 * @param Actor|null              $created_by The actor who created the ban.
	 * @param string                  $comment    The comment providing context for the ban.
	 * @param \DateTimeImmutable|null $created_at The date the ban was created.
	 * @param int                     $id         The ban's id.
	 */
	public function __construct( $host, Actor $created_by = null, $comment = '', \DateTimeImmutable $created_at = null, $id = 0 ) {
		$this->host       = $host;
		$this->created_by = $created_by;
		$this->comment    = $comment;
		$this->created_at = $created_at ?: new \DateTimeImmutable( 'now', new \DateTimeZone( 'UTC' ) );
		$this->id         = $id;
	}

	public function get_id() {
		return $this->id;
	}

	public function get_source() {
		return 'database';
	}

	public function matches( $ip ) {
		return \ITSEC_Lib_IP_Tools::intersect( $ip, $this->host );
	}

	public function get_created_at() {
		return $this->created_at;
	}

	public function get_created_by() {
		return $this->created_by;
	}

	public function get_comment() {
		return $this->comment;
	}

	public function __toString() {
		return $this->host;
	}

	/**
	 * Gets the banned host.
	 *
	 * @return string
	 */
	public function get_host() {
		return $this->host;
	}
}
