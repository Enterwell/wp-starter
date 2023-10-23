<?php

class ITSEC_Dashboard_Card_Banned_Users extends ITSEC_Dashboard_Card {
	public function get_slug() {
		return 'banned-users-list';
	}

	public function get_label() {
		return __( 'Banned IPs', 'better-wp-security' );
	}

	public function get_size() {
		return [
			'minW'     => 1,
			'minH'     => 1,
			'maxW'     => 1,
			'maxH'     => 1,
			'defaultW' => 1,
			'defaultH' => 1,
		];
	}

	public function query_for_data( array $query_args, array $settings ) {
		return [];
	}
}
