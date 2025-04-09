<?php

namespace iThemesSecurity\Site_Scanner;

class Malware_Issue implements Issue {
	use Issue_Trait;

	/** @var array */
	private $malware;

	public function __construct( array $malware ) {
		$this->id          = $malware['id'];
		$this->status      = Status::WARN;
		$this->description = $malware['message'];
		$this->link        = $malware['link'];
		$this->entry       = 'malware';
		$this->malware     = $malware;
	}

	public function get_meta() {
		return array_diff_key( $this->malware, [ 'id' => '', 'message' => '', 'link' => '' ] );
	}
}
