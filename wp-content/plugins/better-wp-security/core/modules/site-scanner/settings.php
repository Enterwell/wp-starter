<?php

final class ITSEC_Site_Scanner_Settings extends ITSEC_Settings {
	public function get_id() {
		return 'site-scanner';
	}

	public function get_defaults() {
		return array(
			'vulnerabilities' => array(),
			'muted_issues'    => array(),
		);
	}
}

ITSEC_Modules::register_settings( new ITSEC_Site_Scanner_Settings() );
