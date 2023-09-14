<?php

namespace iThemesSecurity\Actor;

final class Lockout_Module_Factory implements Actor_Factory {
	public function make( $identifier ) {
		return new Lockout_Module( $identifier );
	}

	public function get_slug() {
		return 'lockout_module';
	}

	public function get_label() {
		return __( 'Lockout Module', 'better-wp-security' );
	}

	public function is_determinate() {
		return true;
	}

	public function get_actors( $query = '' ) {
		/** @var \ITSEC_Lockout $itsec_lockout */
		global $itsec_lockout;

		$modules = $itsec_lockout->get_lockout_modules();

		if ( $query ) {
			$modules = array_filter( $modules, static function ( $module ) use ( $query ) {
				return stripos( $module['type'], $query ) !== false || stripos( $module['reason'], $query ) !== false || stripos( $module['label'], $query ) !== false;
			} );
		}

		return array_map( function ( $module ) {
			return $this->make( $module['type'] );
		}, $modules );
	}
}
