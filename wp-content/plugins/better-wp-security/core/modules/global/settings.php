<?php

use iThemesSecurity\Config_Settings;
use iThemesSecurity\Module_Config;
use iThemesSecurity\Strauss\StellarWP\Telemetry\Opt_In\Opt_In_Subscriber;
use iThemesSecurity\Strauss\StellarWP\Telemetry\Opt_In\Status as Opt_In_Status;

final class ITSEC_Global_Settings extends Config_Settings {

	/** @var Opt_In_Status */
	private $opt_in_status;

	/** @var Opt_In_Subscriber */
	private $opt_in_subscriber;

	public function __construct( Module_Config $config, Opt_In_Status $opt_in_status, Opt_In_Subscriber $opt_in_subscriber ) {
		$this->opt_in_status     = $opt_in_status;
		$this->opt_in_subscriber = $opt_in_subscriber;

		parent::__construct( $config );
	}

	public function load() {
		parent::load();

		$this->settings['allow_tracking'] = $this->opt_in_status->is_active();
	}

	public function get_default( $setting, $default = null ) {
		$default = parent::get_default( $setting, $default );

		switch ( $setting ) {
			case 'nginx_file':
				return ABSPATH . 'nginx.conf';
			case 'log_location':
				return ITSEC_Core::get_storage_dir( 'logs' );
			case 'enable_remote_help';
				return ITSEC_Core::is_pro() ? true : $default;
			case 'proxy':
				$proxies = ITSEC_Lib_IP_Detector::get_proxy_types();

				return isset( $proxies['security-check'] ) ? 'security-check' : $default;
			default:
				return $default;
		}
	}

	public function get_settings_schema() {
		$schema = parent::get_settings_schema();

		$schema['properties']['proxy']['enum']      = array_keys( ITSEC_Lib_IP_Detector::get_proxy_types() );
		$schema['properties']['proxy']['enumNames'] = array_values( ITSEC_Lib_IP_Detector::get_proxy_types() );

		$schema['properties']['proxy_header']['enum']      = ITSEC_Lib_IP_Detector::get_proxy_headers();
		$schema['properties']['proxy_header']['enumNames'] = array_map( static function ( $header ) {
			if ( 0 === strpos( $header, 'HTTP_' ) ) {
				$header = substr( $header, 5 );
			}

			$header = str_replace( '_', '-', $header );
			$header = strtolower( $header );
			$header = ucwords( $header, '-' );
			$header = str_replace( [ 'Ip', 'Cf' ], [ 'IP', 'CF' ], $header );

			return $header;
		}, ITSEC_Lib_IP_Detector::get_proxy_headers() );

		return $schema;
	}

	protected function handle_settings_changes( $old_settings ) {
		parent::handle_settings_changes( $old_settings );

		if ( $this->settings['write_files'] && ! $old_settings['write_files'] ) {
			ITSEC_Response::regenerate_server_config();
			ITSEC_Response::regenerate_wp_config();
		}

		if ( $this->settings['use_cron'] !== $old_settings['use_cron'] ) {
			$this->handle_cron_change( $this->settings['use_cron'] );
		}

		if ( $this->settings['allow_tracking'] !== $old_settings['allow_tracking'] ) {
			if ( $this->settings['allow_tracking'] ) {
				// The opt-in code is not tolerant to being run outside of WP-Admin.
				require_once ABSPATH . 'wp-admin/includes/update.php';
				require_once ABSPATH . 'wp-admin/includes/misc.php';

				$this->opt_in_subscriber->opt_in( 'solid-security' );
			} else {
				$this->opt_in_status->set_status( false, 'solid-security' );
			}
		}
	}

	private function handle_cron_change( $new_use_cron ) {
		$class = $new_use_cron ? 'ITSEC_Scheduler_Cron' : 'ITSEC_Scheduler_Page_Load';
		$this->handle_scheduler_change( $class );
	}

	private function handle_scheduler_change( $new_class ) {
		$choices = array(
			'ITSEC_Scheduler_Cron'      => ITSEC_Core::get_core_dir() . 'lib/class-itsec-scheduler-cron.php',
			'ITSEC_Scheduler_Page_Load' => ITSEC_Core::get_core_dir() . 'lib/class-itsec-scheduler-page-load.php',
		);

		require_once( $choices[ $new_class ] );

		/** @var ITSEC_Scheduler $new */
		$new     = new $new_class();
		$current = ITSEC_Core::get_scheduler();

		$new->uninstall();

		foreach ( $current->get_custom_schedules() as $slug => $interval ) {
			$new->register_custom_schedule( $slug, $interval );
		}

		$new->run();

		foreach ( $current->get_recurring_events() as $event ) {
			$new->schedule( $event['schedule'], $event['id'], $event['data'], array(
				'fire_at' => $event['fire_at'],
			) );
		}

		foreach ( $current->get_single_events() as $event ) {
			$new->schedule_once( $event['fire_at'], $event['id'], $event['data'] );
		}

		$new->run();
		ITSEC_Core::set_scheduler( $new );
		$current->uninstall();
	}
}

ITSEC_Modules::register_settings( new ITSEC_Global_Settings(
	ITSEC_Modules::get_config( 'global' ),
	ITSEC_Modules::get_container()->get( Opt_In_Status::class ),
	ITSEC_Modules::get_container()->get( Opt_In_Subscriber::class ),
) );

class_alias( ITSEC_Global_Settings::class, 'ITSEC_Global_Settings_New' );
