<?php

namespace EwStarter\Configuration\Theme;

class Ew_Theme_Security {
	public function load(){
		// Disable XMLRPC
		add_filter( 'xmlrpc_enabled', '__return_false' );

		// Remove WP version
		add_filter( 'the_generator', '__return_false' );
	}
}
