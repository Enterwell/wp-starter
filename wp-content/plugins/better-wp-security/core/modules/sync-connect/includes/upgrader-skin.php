<?php

class ITSEC_Ithemes_Sync_Upgrader_Skin extends Automatic_Upgrader_Skin {
	public $errors = [];

	public function error( $errors ) {
		$this->errors[] = $errors;
	}

	public function request_filesystem_credentials( $error = false, $context = '', $allow_relaxed_file_ownership = false ) {
		if ( ! function_exists( 'submit_button' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/template.php' );
		}

		$r = parent::request_filesystem_credentials( $error, $context, $allow_relaxed_file_ownership );

		if ( false === $r ) {
			$this->error( __( 'Could not request filesystem credentials.', 'better-wp-security' ) );
		}

		return $r;
	}
}
