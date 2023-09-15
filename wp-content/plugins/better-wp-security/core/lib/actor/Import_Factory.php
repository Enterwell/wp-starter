<?php

namespace iThemesSecurity\Actor;

final class Import_Factory implements Actor_Factory {
	public function make( $identifier ) {
		return new Import();
	}

	public function get_slug() {
		return 'import';
	}

	public function get_label() {
		return __( 'Import', 'better-wp-security' );
	}

	public function is_determinate() {
		return true;
	}

	public function get_actors( $query = '' ) {
		return [
			new Import(),
		];
	}
}
