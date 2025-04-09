<?php

namespace iThemesSecurity\Site_Scanner\Cards;

use iThemesSecurity\Site_Scanner\Repository\Scans_Options;
use iThemesSecurity\Site_Scanner\Repository\Scans_Repository;

class Vulnerable_Software_Card extends \ITSEC_Dashboard_Card {

	/** @var Repository */
	private $repository;

	public function __construct( Scans_Repository $repository ) { $this->repository = $repository; }

	public function get_slug() {
		return 'vulnerable-software';
	}

	public function get_label() {
		return __( 'Vulnerable Software', 'better-wp-security' );
	}

	public function get_size() {
		return [
			'minW'     => 2,
			'maxW'     => 3,
			'minH'     => 1,
			'maxH'     => 1,
			'defaultW' => 2,
			'defaultH' => 1,
		];
	}

	public function query_for_data( array $query_args, array $settings ) {
		$scans = $this->repository->get_scans( ( new Scans_Options() )->set_per_page( 1 ) );

		$request = new \WP_REST_Request( 'GET', '/ithemes-security/v1/site-scanner/vulnerabilities' );
		$request->set_query_params( [ 'resolution' => [ '', 'patched' ] ] );

		$response = rest_do_request( $request );

		if ( $response->is_error() ) {
			return $response->as_error();
		}

		$vulnerabilities = $response->get_data();
		usort( $vulnerabilities, function ( $a, $b ) {
			return ( (float) $b['details']['score'] ?? 0.0 ) <=> (float) ( $a['details']['score'] ?? 0.0 );
		} );

		$date = null;

		if ( $scans ) {
			$date = $scans[0]->get_time()->format( 'Y-m-d\TH:i:s' );
		}

		return [
			'vulnerabilities' => $vulnerabilities,
			'date'            => $date,
		];
	}
}
