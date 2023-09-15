<?php

use iThemesSecurity\Config_Settings;

final class ITSEC_Global_Settings extends Config_Settings {
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

ITSEC_Modules::register_settings( new ITSEC_Global_Settings( ITSEC_Modules::get_config( 'global' ) ) );

class_alias( ITSEC_Global_Settings::class, 'ITSEC_Global_Settings_New' );
