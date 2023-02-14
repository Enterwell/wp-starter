<?php

namespace iThemesSecurity\Site_Scanner;

trait Issue_Trait {

	/** @var string */
	private $id;

	/** @var string */
	private $status;

	/** @var string */
	private $description;

	/** @var string */
	private $link;

	/** @var string */
	private $entry;

	public function get_id() {
		return $this->id;
	}

	public function get_status() {
		return $this->status;
	}

	public function get_description() {
		return $this->description;
	}

	public function get_link() {
		return $this->link;
	}

	public function get_entry() {
		return $this->entry;
	}
}
