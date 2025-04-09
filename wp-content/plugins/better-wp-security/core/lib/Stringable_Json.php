<?php

namespace iThemesSecurity\Lib;

class Stringable_Json implements \JsonSerializable {

	private $value;

	public function __construct( $value ) {
		$this->value = $value;
	}

	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->value;
	}

	public function __toString() {
		return wp_json_encode( $this );
	}
}
