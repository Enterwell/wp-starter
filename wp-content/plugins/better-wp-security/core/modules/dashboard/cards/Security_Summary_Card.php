<?php

namespace iThemesSecurity\Dashboard\Cards;

use iThemesSecurity\Site_Scanner\Repository\Scans_Repository;

class Security_Summary_Card extends \ITSEC_Dashboard_Card {

	/** @var Repository */
	private $repository;

	public function __construct( Scans_Repository $repository ) { $this->repository = $repository; }

	public function get_slug() {
		return 'security-summary';
	}

	public function get_label() {
		return __( 'Security Summary', 'better-wp-security' );
	}

	public function get_size() {
		return [
			'minW'     => 2,
			'minH'     => 1,
			'maxW'     => 2,
			'maxH'     => 1,
			'defaultW' => 2,
			'defaultH' => 1,
		];
	}

	public function query_for_data( array $query_args, array $settings ) {
		$post = get_site_transient( 'ithemes-security-news' );

		if ( false === $post ) {
			$response = wp_safe_remote_get( add_query_arg( [
				'categories' => [ 380, 419, 4766 ],
				'per_page'   => 1,
				'context'    => 'embed',
			], 'https://solidwp.com/wp-json/wp/v2/posts' ) );

			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
				$post = [];
				set_site_transient( 'ithemes-security-news', $post, HOUR_IN_SECONDS );
			} else {
				$json = json_decode( wp_remote_retrieve_body( $response ), true );

				if ( is_array( $json ) ) {
					$post = $json[0];
					set_site_transient( 'ithemes-security-news', $post, DAY_IN_SECONDS );
				} else {
					$post = [];
					set_site_transient( 'ithemes-security-news', $post, HOUR_IN_SECONDS );
				}
			}
		}

		return [
			'news'          => $post ? [
				'title'   => html_entity_decode( $post['title']['rendered'] ),
				'excerpt' => wp_strip_all_tags( html_entity_decode( $post['excerpt']['rendered'] ) ),
				'date'    => $post['date'],
				'link'    => $post['link'],
			] : null,
			'vulnerability' => $this->get_vulnerability(),
		];
	}

	private function get_vulnerability() {
		$request = new \WP_REST_Request( 'GET', '/ithemes-security/v1/site-scanner/vulnerabilities' );
		$request->set_query_params( [ 'resolution' => [ '', 'patched' ] ] );

		$response = rest_do_request( $request );

		if ( $response->is_error() ) {
			return null;
		}

		$vulnerabilities = $response->get_data();

		if ( ! $vulnerabilities ) {
			return null;
		}

		usort( $vulnerabilities, function ( $a, $b ) {
			return $b['details']['score'] <=> $a['details']['score'];
		} );

		return $vulnerabilities[0];
	}
}
