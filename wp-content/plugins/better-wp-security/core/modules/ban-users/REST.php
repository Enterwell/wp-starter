<?php

namespace iThemesSecurity\Ban_Users;

use iThemesSecurity\Actor\User;
use iThemesSecurity\Contracts\Runnable;

final class REST implements Runnable {

	/** @var Database_Repository */
	private $repository;

	/** @var array */
	private $schema;

	/**
	 * REST constructor.
	 *
	 * @param Database_Repository $repository
	 */
	public function __construct( Database_Repository $repository ) { $this->repository = $repository; }

	public function run() {
		$this->setup_schema();
		$this->register_routes();
		add_filter( 'itsec_ban_hosts_rest_schema', [ $this, 'add_many_link' ] );
	}

	private function setup_schema() {
		$this->schema = [
			'title'      => __( 'Add Many', 'better-wp-security' ),
			'type'       => 'object',
			'required'   => [ 'bans' ],
			'properties' => [
				'bans' => [
					'title'       => __( 'IPs to Ban', 'better-wp-security' ),
					'description' => __( 'Enter one IP address per-line. Optionally, include a note by ending the line with a # sign.', 'better-wp-security' ),
					'type'        => 'array',
					'items'       => [
						'type'    => 'string',
						'default' => '',
					],
					'minItems'    => 1,
				],
			],
			'uiSchema'   => [
				'bans' => [
					'ui:field'       => 'TextareaListField',
					'ui:rows'        => 10,
					'ui:placeholder' => '127.0.0.1 # This is my note',
				],
			]
		];
	}

	private function register_routes() {
		register_rest_route( 'ithemes-security/rpc', 'ban-users/add-many', [
			[
				'args'                => \ITSEC_Lib_REST::get_endpoint_args_for_schema( $this->schema ),
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'add_many_callback' ],
				'permission_callback' => 'ITSEC_Core::current_user_can_manage',
			],
			'schema' => function () { return $this->schema; }
		] );
	}

	public function add_many_callback( \WP_REST_Request $request ) {
		$bans   = $request['bans'];
		$to_add = [];
		$error  = new \WP_Error();

		foreach ( $bans as $i => $ban ) {
			list( $ip, $note ) = array_pad( explode( ' #', $ban ), 2, '' );

			$valid = \ITSEC_Lib_REST::validate_ip( $ip, $request, "bans.{$i}" );

			if ( is_wp_error( $valid ) ) {
				$error->merge_from( $valid );
				continue;
			}

			$ip   = \ITSEC_Lib_IP_Tools::ip_wild_to_ip_cidr( $ip );
			$note = trim( $note, " \t\n\r\0\x0B#" );

			$ban      = new Ban( $ip, new User( wp_get_current_user() ), $note );
			$to_add[] = $ban;
		}

		if ( $error->has_errors() ) {
			$error->add_data( [ 'status' => \WP_Http::BAD_REQUEST ] );

			return $error;
		}

		$persisted = [];

		foreach ( $to_add as $ban ) {
			try {
				$persisted[] = $this->repository->persist( $ban )->get_id();
			} catch ( \Exception $e ) {

			}
		}

		return $persisted;
	}

	public function add_many_link( $schema ) {
		$schema['links'][] = [
			'rel'              => 'create-form',
			'href'             => rest_url( 'ithemes-security/rpc/ban-users/add-many' ),
			'submissionSchema' => \ITSEC_Lib_REST::sanitize_schema_for_output( $this->schema ),
			'targetHints'      => [
				'allow' => \ITSEC_Core::current_user_can_manage() ? [ 'POST' ] : [],
			],
			'title'            => $this->schema['title'],
		];

		return $schema;
	}
}
