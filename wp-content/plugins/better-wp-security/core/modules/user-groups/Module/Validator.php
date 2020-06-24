<?php

namespace iThemesSecurity\User_Groups\Module;

use iThemesSecurity\Contracts\Runnable;

class Validator extends \ITSEC_Validator implements Runnable {
	public function get_id() {
		return 'user-groups';
	}

	public function run() {
		\ITSEC_Modules::register_validator( $this );
	}

	protected function sanitize_settings() {
		$this->sanitize_setting( 'user-groups', 'default_groups', __( 'Default Groups', 'better-wp-security' ) );
	}
}
