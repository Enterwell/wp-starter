<?php

use \iThemesSecurity\User_Groups;

class ITSEC_Global_Validator extends ITSEC_Validator {
	public function get_id() {
		return 'global';
	}

	protected function sanitize_settings() {
		if ( is_dir( WP_PLUGIN_DIR . '/iwp-client' ) ) {
			$this->sanitize_setting( 'bool', 'infinitewp_compatibility', __( 'Add InfiniteWP Compatibility', 'better-wp-security' ) );
		} else {
			$this->settings['infinitewp_compatibility'] = $this->previous_settings['infinitewp_compatibility'];
		}

		if ( 'nginx' === ITSEC_Lib::get_server() ) {
			$this->sanitize_setting( 'writable-file', 'nginx_file', __( 'NGINX Conf File', 'better-wp-security' ), false );
		} else {
			$this->settings['nginx_file'] = $this->previous_settings['nginx_file'];
		}


		$this->vars_to_skip_validate_matching_fields = array( 'digest_last_sent', 'digest_messages', 'digest_email', 'email_notifications', 'notification_email', 'backup_email', 'show_new_dashboard_notice', 'proxy_override', 'proxy', 'proxy_header', 'server_ips', 'initial_build', 'feature_flags', 'licensed_hostname_prompt' );
		$this->set_previous_if_empty( array( 'did_upgrade', 'log_info', 'show_security_check', 'build', 'activation_timestamp', 'lock_file', 'cron_status', 'use_cron', 'cron_test_time', 'proxy', 'proxy_header', 'server_ips', 'initial_build', 'feature_flags', 'licensed_hostname_prompt' ) );
		$this->set_default_if_empty( array( 'log_location', 'nginx_file', 'enable_grade_report' ) );
		$this->preserve_setting_if_exists( array( 'digest_email', 'email_notifications', 'notification_email', 'backup_email', 'proxy_override' ) );

		$this->sanitize_setting( 'bool', 'write_files', __( 'Write to Files', 'better-wp-security' ) );
		$this->sanitize_setting( 'bool', 'blacklist', __( 'Ban Repeat Offender', 'better-wp-security' ) );
		$this->sanitize_setting( 'bool', 'allow_tracking', __( 'Allow Data Tracking', 'better-wp-security' ) );
		$this->sanitize_setting( array_keys( $this->get_proxy_types() ), 'proxy', __( 'Proxy Detection', 'better-wp-security' ) );
		$this->sanitize_setting( 'string', 'proxy_header', __( 'Manual Proxy Header', 'better-wp-security' ) );
		$this->sanitize_setting( 'bool', 'hide_admin_bar', __( 'Hide Security Menu in Admin Bar', 'better-wp-security' ) );
		$this->sanitize_setting( 'bool', 'show_error_codes', __( 'Show Error Codes', 'better-wp-security' ) );
		$this->sanitize_setting( 'bool', 'enable_grade_report', __( 'Enable Grade Report', 'better-wp-security' ) );

		$this->sanitize_setting( 'string', 'lockout_message', __( 'Host Lockout Message', 'better-wp-security' ) );
		$this->sanitize_setting( 'string', 'user_lockout_message', __( 'User Lockout Message', 'better-wp-security' ) );
		$this->sanitize_setting( 'string', 'community_lockout_message', __( 'Community Lockout Message', 'better-wp-security' ) );

		$this->sanitize_setting( 'positive-int', 'blacklist_count', __( 'Ban Threshold', 'better-wp-security' ) );
		$this->sanitize_setting( 'positive-int', 'blacklist_period', __( 'Ban Lockout Period', 'better-wp-security' ) );
		$this->sanitize_setting( 'positive-int', 'lockout_period', __( 'Lockout Period', 'better-wp-security' ) );
		$this->sanitize_setting( 'positive-int', 'log_rotation', __( 'Days to Keep Database Logs', 'better-wp-security' ) );
		$this->sanitize_setting( 'positive-int', 'file_log_rotation', __( 'Days to Keep File Logs', 'better-wp-security' ) );

		$this->sanitize_setting( 'newline-separated-ips', 'lockout_white_list', __( 'Authorized Hosts List', 'better-wp-security' ) );

		$log_types = array_keys( $this->get_valid_log_types() );
		$this->sanitize_setting( $log_types, 'log_type', __( 'Log Type', 'better-wp-security' ) );

		if ( 'database' !== $this->settings['log_type'] ) {
			$this->sanitize_setting( 'writable-directory', 'log_location', __( 'Path to Log Files', 'better-wp-security' ) );
		}

		$allowed_tags = $this->get_allowed_tags();

		$this->settings['lockout_message']           = trim( wp_kses( $this->settings['lockout_message'], $allowed_tags ) );
		$this->settings['user_lockout_message']      = trim( wp_kses( $this->settings['user_lockout_message'], $allowed_tags ) );
		$this->settings['community_lockout_message'] = trim( wp_kses( $this->settings['community_lockout_message'], $allowed_tags ) );

		$this->sanitize_setting( 'newline-separated-ips', 'server_ips', __( 'Server IPs', 'better-wp-security' ) );
		$this->sanitize_setting( 'array', 'feature_flags', __( 'Feature Flags', 'better-wp-security' ) );
		$this->sanitize_setting( 'user-groups', 'manage_group', __( 'Manage Group', 'better-wp-security' ) );
		$this->sanitize_setting( 'bool', 'licensed_hostname_prompt', __( 'Licensed Hostname Prompt', 'better-wp-security' ) );
	}

	protected function validate_settings() {
		if ( ITSEC_Core::is_interactive() && $this->settings['manage_group'] && $this->settings['manage_group'] !== $this->previous_settings['manage_group'] ) {
			$matcher = ITSEC_Modules::get_container()->get( User_Groups\Matcher::class );

			if ( ! $matcher->matches( User_Groups\Match_Target::for_user( wp_get_current_user() ), $this->settings['manage_group'] ) ) {
				$this->add_error( new WP_Error( 'itsec-validator-global-cannot-exclude-self', __( 'The configuration you have chosen removes your capability to manage iThemes Security.', 'better-wp-security' ), [ 'status' => 400 ] ) );
				$this->set_can_save( false );
			}
		}
	}

	public function get_proxy_types() {
		ITSEC_Lib::load( 'ip-detector' );

		return ITSEC_Lib_IP_Detector::get_proxy_types();
	}

	public function get_proxy_header_options() {
		ITSEC_Lib::load( 'ip-detector' );

		$possible_headers = ITSEC_Lib_IP_Detector::get_proxy_headers();
		$possible_headers[] = 'REMOTE_ADDR';

		$ucwords = version_compare( phpversion(), '5.5.16', '>=' ) || ( version_compare( phpversion(), '5.4.32', '>=' ) && version_compare( phpversion(), '5.5.0', '<' ) );
		$options = array();

		foreach ( $possible_headers as $header ) {
			$label = $header;

			if ( 0 === strpos( $header, 'HTTP_' ) ) {
				$label = substr( $label, 5 );
			}

			$label = str_replace( '_', '-', $label );
			$label = strtolower( $label );
			$label = $ucwords ? ucwords( $label, '-' ) : implode( '-', array_map( 'ucfirst', explode( '-', $label ) ) );
			$label = str_replace('Ip', 'IP', $label );

			$options[ $header ] = $label;
		}

		return $options;
	}

	public function get_valid_log_types() {
		return array(
			'database' => __( 'Database Only', 'better-wp-security' ),
			'file'     => __( 'File Only', 'better-wp-security' ),
			'both'     => __( 'Both', 'better-wp-security' ),
		);
	}

	private function get_allowed_tags() {
		return array(
			'a'      => array(
				'href'  => array(),
				'title' => array(),
			),
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
			'h1'     => array(),
			'h2'     => array(),
			'h3'     => array(),
			'h4'     => array(),
			'h5'     => array(),
			'h6'     => array(),
			'div'    => array(
				'style' => array(),
			),
		);
	}
}

ITSEC_Modules::register_validator( new ITSEC_Global_Validator() );
