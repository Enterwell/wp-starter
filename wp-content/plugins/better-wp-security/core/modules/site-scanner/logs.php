<?php

class ITSEC_Site_Scanner_Logs {
	public function __construct() {
		add_filter( 'itsec_logs_prepare_site-scanner_entry_for_list_display', array( $this, 'filter_entry_for_list_display' ), 10, 3 );
		add_filter( 'itsec_logs_prepare_site-scanner_entry_for_details_display', array( $this, 'filter_entry_for_details_display' ), 10, 4 );
		add_filter( 'itsec_highlighted_log_site-scanner-report_notice_title', array( $this, 'filter_highlight_title' ), 10, 2 );
		add_filter( 'itsec_highlighted_log_site-scanner-report_notice_message', array( $this, 'filter_highlight_message' ), 10, 2 );

		if ( did_action( 'admin_enqueue_scripts' ) ) {
			$this->enqueue();
		} else {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		}
	}

	public function filter_entry_for_list_display( $entry, $code, $code_data ) {
		$entry['module_display'] = esc_html__( 'Site Scan', 'better-wp-security' );

		switch ( $code ) {
			case 'scan':
				if ( 'process-start' === $entry['type'] ) {
					$entry['description'] = esc_html__( 'Scan Performance', 'better-wp-security' );
				} else {
					$entry['description'] = esc_html__( 'Scan', 'better-wp-security' );
				}
				break;
			case 'verify-scan':
				if ( $code_data[0] === 'invalid_secret' ) {
					$entry['description'] = __( 'Scan Verification Failed: Invalid Secret', 'better-wp-security' );
				} else {
					$entry['description'] = __( 'Site Verification Succeeded', 'better-wp-security' );
				}
				break;
			default:
				require_once( dirname( __FILE__ ) . '/util.php' );
				$entry['description'] = ITSEC_Site_Scanner_Util::get_scan_code_description( $entry['code'] );

				if ( ! $entry['description'] ) {
					$entry['description'] = $entry['code'];
				}
		}

		return $entry;
	}

	public function filter_entry_for_details_display( $details, $entry, $code, $code_data ) {
		require_once( dirname( __FILE__ ) . '/template.php' );

		$entry = $this->filter_entry_for_list_display( $entry, $code, $code_data );

		$details['module']['content']      = $entry['module_display'];
		$details['description']['content'] = $entry['description'];

		if (
			isset( $entry['data']['results'] ) &&
			( is_array( $entry['data']['results'] ) || is_wp_error( $entry['data']['results'] ) ) &&
			! in_array( $entry['type'], [ 'process-start', 'process-update', 'process-stop' ], true )
		) {
			$details['results'] = array(
				'header'  => esc_html__( 'Results', 'better-wp-security' ),
				'content' => ITSEC_Site_Scanner_Template::get_html( $entry['data']['results'] ),
			);
		}

		return $details;
	}

	public function filter_highlight_title( $title, $entry ) {
		$factory = ITSEC_Modules::get_container()->get( \iThemesSecurity\Site_Scanner\Factory::class );

		$scan = $factory->for_log_item( $entry );

		if ( is_wp_error( $scan ) ) {
			return esc_html__( 'The iThemes Security site scan encountered an error.', 'better-wp-security' );
		}

		$count = $scan->count( \iThemesSecurity\Site_Scanner\Status::WARN );

		return esc_html( sprintf(
			_n(
				'iThemes Security found %s issue during a site scan.',
				'iThemes Security found %s issues during a site scan.',
				$count,
				'better-wp-security'
			),
			number_format_i18n( $count )
		) );
	}

	public function filter_highlight_message( $title, $entry ) {
		return sprintf(
			esc_html__( 'Please %1$sreview the logs%2$s to make sure your system is secure.', 'better-wp-security' ),
			'<a href="{{ $view }}">',
			'</a>'
		);
	}

	public function enqueue() {
		wp_enqueue_script( 'itsec-site-scanner-scan-settings' );
		wp_enqueue_style( 'itsec-core-packages-components-site-scan-results-style' );
	}
}

new ITSEC_Site_Scanner_Logs();
