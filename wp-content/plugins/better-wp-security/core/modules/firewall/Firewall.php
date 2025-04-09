<?php

namespace iThemesSecurity\Modules\Firewall;

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Modules\Firewall\Rules\Repository;
use iThemesSecurity\Modules\Firewall\Rules\Rules_Options;

class Firewall implements Runnable {
	/** @var Repository */
	private $rules;

	/** @var Processor_Factory */
	private $processor_factory;

	/** @var Runnable[] */
	private $runnable;

	public function __construct( Repository $rules, Processor_Factory $processor_factory, Runnable ...$runnable ) {
		$this->rules             = $rules;
		$this->processor_factory = $processor_factory;
		$this->runnable          = $runnable;
	}

	public function run() {
		array_walk( $this->runnable, function ( Runnable $runnable ) {
			$runnable->run();
		} );

		add_filter( 'itsec_lockout_modules', [ $this, 'register_module' ], 10, 2 );
		add_filter( 'debug_information', [ $this, 'add_site_health_info' ] );

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

	/**
	 * Add Firewall Rule counts to Site Health data.
	 *
	 * @param array $info
	 *
	 * @return array
	 */
	public function add_site_health_info( $info ) {
		$patchstack = $this->rules->count_rules( ( new Rules_Options() )->set_providers( [ 'patchstack' ] ) );
		$user       = $this->rules->count_rules( ( new Rules_Options() )->set_providers( [ 'user' ] ) );

		if ( $patchstack->is_success() ) {
			$info['solid-security']['fields']['patchstack-firewall-rules'] = [
				'label' => __( 'Patchstack Firewall Rules', 'better-wp-security' ),
				'value' => $patchstack->get_data(),
				'debug' => $patchstack->get_data(),
			];
		}

		if ( $user->is_success() ) {
			$info['solid-security']['fields']['user-firewall-rules'] = [
				'label' => __( 'User Firewall Rules', 'better-wp-security' ),
				'value' => $user->get_data(),
				'debug' => $user->get_data(),
			];
		}

		return $info;
	}

	public function register_module( $modules, $translate ) {
		$modules['firewall'] = [
			'type'   => 'firewall',
			'reason' => $translate ? __( 'Triggered too many firewall rules', 'better-wp-security' ) : 'Triggered too many firewall rules',
			'label'  => $translate ? __( 'Firewall Rule', 'better-wp-security' ) : 'Firewall Rule',
			'host'   => \ITSEC_Modules::get_setting( 'firewall', 'max_attempts_host' ),
			'period' => \ITSEC_Modules::get_setting( 'firewall', 'check_period' ),
		];

		return $modules;
	}
}
