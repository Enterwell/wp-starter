<?php

namespace iThemesSecurity\Modules\Firewall;

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Site_Scanner\Repository\Vulnerabilities_Repository;

class Logs implements Runnable {

	/** @var Vulnerabilities_Repository */
	private $vulnerabilities;

	public function __construct( Vulnerabilities_Repository $vulnerabilities ) { $this->vulnerabilities = $vulnerabilities; }

	public function run() {
		add_filter( 'itsec_logs_prepare_firewall_entry_for_list_display', [ $this, 'format_for_list' ], 10, 3 );
		add_filter( 'itsec_logs_prepare_firewall_entry_for_details_display', [ $this, 'format_for_details' ], 10, 4 );
		add_filter( 'itsec_rest_prepare_log_for_response', [ $this, 'add_links_to_rest_api' ], 10, 2 );
	}

	public function format_for_list( array $data, string $code, array $code_data ): array {
		$data['module_display'] = __( 'Firewall Rules Engine', 'better-wp-security' );
		$data['description']    = $this->get_description( $code, $code_data );

		return $data;
	}

	public function format_for_details( array $details, array $data, string $code, array $code_data ): array {
		$details['module']['content']      = __( 'Firewall Rules Engine', 'better-wp-security' );
		$details['description']['content'] = $this->get_description( $code, $code_data );

		if ( $vulnerability = $this->get_vulnerability( $code, $code_data ) ) {
			$details['vulnerability'] = [
				'header'  => __( 'Vulnerability', 'better-wp-security' ),
				'content' => $vulnerability,
				'order'   => 21,
			];
		}

		return $details;
	}

	public function add_links_to_rest_api( \WP_REST_Response $response, array $item ): \WP_REST_Response {
		if ( $item['module'] !== 'firewall' ) {
			return $response;
		}

		if ( ! \ITSEC_Lib::str_starts_with( $item['code'], 'BLOCK::' ) ) {
			return $response;
		}

		[ , $data ] = explode( '::', $item['code'], 2 );

		$response->add_link(
			\ITSEC_Lib_REST::get_link_relation( 'firewall-rule' ),
			rest_url( sprintf( 'ithemes-security/v1/firewall/rules/%d', $data ) ),
			[
				'embeddable' => true,
			]
		);

		return $response;
	}

	protected function get_description( string $code, array $code_data ): string {
		switch ( $code ) {
			case 'auto-created-rule':
				return __( 'Auto-created a firewall rule.', 'better-wp-security' );
			case 'ingest-failed':
				return __( 'Failed to create a firewall rule.', 'better-wp-security' );
			case 'rule-cleaned':
				return __( 'Auto-removed an old firewall rule.', 'better-wp-security' );
			case 'rule-cleanup-failed':
				return __( 'Failed to remove an old firewall rule.', 'better-wp-security' );
			case 'BLOCK':
				return __( 'Blocked a suspicious request.', 'better-wp-security' );
			case 'REDIRECT':
				return __( 'Redirected a suspicious request.', 'better-wp-security' );
			case 'LOG':
				return __( 'Logging a request for further analysis.', 'better-wp-security' );
			default:
				return $code;
		}
	}

	protected function get_vulnerability( string $code, array $code_data ): string {
		switch ( $code ) {
			case 'auto-created-rule':
			case 'ingest-failed':
			case 'rule-cleaned':
			case 'rule-cleanup-failed':
				$vulnerability_id = $code_data[0];
				break;
			case 'BLOCK':
			case 'REDIRECT':
			case 'LOG':
				$rule_id = $code_data[0];

				if ( false === strpos( $rule_id, '|' ) ) {
					return '';
				}

				[ , $vulnerability_id ] = explode( '|', $rule_id );
				break;
			default:
				return '';
		}

		$found = $this->vulnerabilities->find( $vulnerability_id );

		if ( ! $found->is_success() || ! $vulnerability = $found->get_data() ) {
			return sprintf(
				/* translators: A id value. */
				__( 'Unknown vulnerability (%s)', 'better-wp-security' ),
				$vulnerability_id
			);
		}

		return sprintf(
			'<a href="%s">%s</a>',
			network_admin_url( 'admin.php?page=itsec-vulnerabilities&path=' . urlencode( '/vulnerability/' . $vulnerability_id ) ),
			$vulnerability->get_details()['title'] ?? $vulnerability_id
		);
	}
}
