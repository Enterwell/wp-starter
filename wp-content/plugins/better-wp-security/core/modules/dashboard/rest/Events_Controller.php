<?php

namespace iThemesSecurity\Dashboard\REST;

class Events_Controller extends \WP_REST_Controller {

	public function __construct() {
		$this->namespace = 'ithemes-security/v1';
		$this->rest_base = 'dashboard/events';
	}

	public function register_routes() {
		register_rest_route( $this->namespace, $this->rest_base, [
			[
				'callback'            => [ $this, 'get_items' ],
				'permission_callback' => [ $this, 'get_items_permissions_check' ],
				'args'                => $this->get_collection_params(),
			],
			'schema' => [ $this, 'get_public_item_schema' ],
		] );
	}

	public function get_items_permissions_check( $request ) {
		return \ITSEC_Core::current_user_can_manage();
	}

	public function get_items( $request ) {
		$events = \ITSEC_Dashboard_Util::query_events( $request['events'], $request['period'] );

		if ( is_wp_error( $events ) ) {
			return $events;
		}

		$data = [];

		foreach ( $events as $event ) {
			foreach ( $event as $item ) {
				$date = \ITSEC_Lib::to_rest_date( $item['time'] );

				if ( isset( $data[ $date ] ) ) {
					$data[ $date ]['y'] += $item['count'];
				} else {
					$data[ $date ] = [
						't' => $date,
						'y' => $item['count'],
					];
				}
			}
		}

		return new \WP_REST_Response( [ 'data' => $data ] );
	}

	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->schema;
		}

		$this->schema = [
			'type'       => 'object',
			'properties' => [
				'data' => [
					'additionalProperties' => [
						'type'       => 'object',
						'properties' => [
							't' => [
								'type'   => 'string',
								'format' => 'date-time',
							],
							'y' => [
								'type' => 'integer',
							],
						],
					],
				],
			],
		];

		return $this->schema;
	}

	public function get_collection_params() {
		return [
			'period' => \ITSEC_Dashboard_REST::get_period_arg(),
			'events' => [
				'type'        => 'array',
				'items'       => [
					'type' => 'string',
				],
				'uniqueItems' => true,
				'minItems'    => 1,
				'maxItems'    => 10,
				'required'    => true,
			],
		];
	}
}
