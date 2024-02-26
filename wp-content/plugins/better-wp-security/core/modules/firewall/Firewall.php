<?php

namespace iThemesSecurity\Modules\Firewall;

use iThemesSecurity\Contracts\Runnable;

class Firewall implements Runnable {
	/** @var Processor_Factory */
	private $processor_factory;

	/** @var Runnable[] */
	private $runnable;

	public function __construct( Processor_Factory $processor_factory, Runnable ...$runnable ) {
		$this->processor_factory = $processor_factory;
		$this->runnable          = $runnable;
	}

	public function run() {
		array_walk( $this->runnable, function ( Runnable $runnable ) {
			$runnable->run();
		} );

		add_filter( 'itsec_lockout_modules', [ $this, 'register_module' ] );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return;
		}

		add_action( 'init', [ $this, 'launch_firewall' ], PHP_INT_MIN );

		if ( \ITSEC_Core::is_loading_early() ) {
			$this->processor_factory->make( true )->launch();
			define( 'PS_FW_MU_RAN', true );
		}
	}

	public function launch_firewall() {
		$this->processor_factory->make( false )->launch();
	}

	public function register_module( $modules ) {
		$modules['firewall'] = [
			'type'   => 'firewall',
			'reason' => __( 'Triggered too many firewall rules', 'better-wp-security' ),
			'label'  => __( 'Firewall Rule', 'better-wp-security' ),
			'host'   => \ITSEC_Modules::get_setting( 'firewall', 'max_attempts_host' ),
			'period' => \ITSEC_Modules::get_setting( 'firewall', 'check_period' ),
		];

		return $modules;
	}
}
