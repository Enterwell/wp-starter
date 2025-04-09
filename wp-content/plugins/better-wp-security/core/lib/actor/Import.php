<?php

namespace iThemesSecurity\Actor;

final class Import implements Actor {
	public function get_type() {
		return 'import';
	}

	public function get_identifier() {
		return 'import';
	}

	public function __toString() {
		return __( 'Import', 'better-wp-security');
	}
}
