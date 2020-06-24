<?php

namespace iThemesSecurity\User_Groups;

class Settings_Page extends \ITSEC_Module_Settings_Page {

	public function __construct() {
		$this->id            = 'user-groups';
		$this->title         = __( 'User Groups', 'better-wp-security' );
		$this->description   = __( 'Manage user groups.', 'better-wp-security' );
		$this->type          = 'recommended';
		$this->can_save      = false;
		$this->documentation = 'https://ithemeshelp.zendesk.com/hc/en-us/articles/360042653774';

		parent::__construct();
	}

	public function enqueue_scripts_and_styles() {
		$preload = \ITSEC_Lib::preload_rest_requests( [
			'/ithemes-security/v1/user-matchables?_embed=1' => [
				'route' => '/ithemes-security/v1/user-matchables',
				'embed' => true,
			],
			'/ithemes-security/v1?context=help'             => [
				'route' => '/ithemes-security/v1',
				'query' => [ 'context' => 'help' ],
			]
		] );

		wp_enqueue_script( 'itsec-user-groups-settings' );
		wp_enqueue_style( 'itsec-user-groups-settings' );
		wp_add_inline_script(
			'itsec-user-groups-settings',
			sprintf( 'wp.apiFetch.use( wp.apiFetch.createPreloadingMiddleware( %s ) );', wp_json_encode( $preload ) )
		);
	}

	public function render( $form ) {
		echo '<div id="itsec-user-groups-settings-root"></div>';
	}
}

new Settings_Page();
