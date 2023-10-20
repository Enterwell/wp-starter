<?php

class ITSEC_Dashboard_Card_Malware_Scan extends ITSEC_Dashboard_Card {
	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'malware-scan';
	}

	/**
	 * @inheritDoc
	 */
	public function get_label() {
		return __( 'Latest Site Scans', 'better-wp-security' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_size() {
		$scan_only = ITSEC_Modules::get_setting( 'global', 'log_type' ) === 'file';

		return array(
			'minW'     => $scan_only ? 1 : 2,
			'minH'     => 1,
			'maxW'     => $scan_only ? 1 : 2,
			'maxH'     => 1,
			'defaultW' => $scan_only ? 1 : 2,
			'defaultH' => 1,
		);
	}

	/**
	 * @inheritDoc
	 */
	public function query_for_data( array $query_args, array $settings ) {

		if ( isset( $query_args['period'] ) ) {
			$period = $query_args['period'];
		} else {
			$qa_schema = $this->get_query_args();
			$period    = $qa_schema['period']['default'];
		}

		if ( is_wp_error( $range = ITSEC_Dashboard_Util::_get_range( $period ) ) ) {
			return $range;
		}

		$request = new WP_REST_Request( 'GET', '/ithemes-security/v1/site-scanner/scans' );
		$request->set_query_params( [
			'after'  => ITSEC_Lib::to_rest_date( $range[0] ),
			'before' => ITSEC_Lib::to_rest_date( $range[1] ),
		] );

		$response = rest_do_request( $request );

		return [
			'scans'    => rest_get_server()->response_to_data( $response, true ),
			'log_type' => ITSEC_Modules::get_setting( 'global', 'log_type' ),
		];
	}

	/**
	 * @inheritDoc
	 */
	public function get_query_args() {
		$args = parent::get_query_args();

		$args['period'] = ITSEC_Dashboard_REST::get_period_arg();

		return $args;
	}

	/**
	 * @inheritdoc
	 */
	public function get_links() {
		return [
			[
				'href'  => ITSEC_Core::get_logs_page_url( [ 'module' => 'site-scanner' ] ),
				'rel'   => ITSEC_Lib_REST::LINK_REL . 'logs',
				'title' => __( 'View Scan Logs', 'better-wp-security' ),
				'media' => 'text/html',
				'cap'   => ITSEC_Core::get_required_cap(),
			],
			[
				'rel'      => ITSEC_Lib_REST::LINK_REL . 'rpc',
				'title'    => __( 'Scan Now', 'better-wp-security' ),
				'endpoint' => 'scan',
				'cap'      => ITSEC_Core::get_required_cap(),
				'callback' => [ $this, 'do_malware_scan' ],
			]
		];
	}

	/**
	 * Perform the malware scan.
	 *
	 * @return array|WP_Error
	 */
	public function do_malware_scan() {
		$request  = new WP_REST_Request( 'POST', '/ithemes-security/v1/site-scanner/scans' );
		$response = rest_do_request( $request );

		if ( $response->is_error() ) {
			return $response->as_error();
		}

		return rest_get_server()->response_to_data( $response, true );
	}
}
