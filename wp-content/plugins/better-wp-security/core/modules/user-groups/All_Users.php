<?php

namespace iThemesSecurity\User_Groups;

final class All_Users implements Matchable {
	public function get_id() {
		return ':all';
	}

	public function get_label() {
		return __( 'All Users', 'better-wp-security' );
	}

	public function matches( Match_Target $target ) {
		return true;
	}
}
