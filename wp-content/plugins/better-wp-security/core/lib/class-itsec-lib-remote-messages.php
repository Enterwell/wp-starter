<?php

class ITSEC_Lib_Remote_Messages {

	const URL = 'https://ithemes.com/api/itsec-service-status.json';
	const OPTION = 'itsec_remote_messages';
	const EVENT = 'remote-messages';

	/** @var array */
	private static $_response;

	/**
	 * Initialize the Remote Messages library.
	 */
	public static function init() {
		if ( ITSEC_Core::is_pro() ) {
			add_action( 'itsec_scheduled_' . self::EVENT, array( __CLASS__, 'run_event' ) );
		}
	}

	public static function get_actions() {

		$response = self::get_response();

		return isset( $response['actions'] ) ? $response['actions'] : array();
	}

	public static function has_action( $action ) {
		return in_array( $action, self::get_actions(), true );
	}

	public static function get_feature( $flag ) {
		$response = self::get_response();

		return isset( $response['features'][ $flag ] ) ? $response['features'][ $flag ] : null;
	}

	public static function get_raw_messages() {
		$response = self::get_response();

		return isset( $response['messages'] ) ? $response['messages'] : array();
	}

	public static function get_messages_for_placement( $placement ) {

		$matched = array();

		foreach ( self::get_raw_messages() as $message ) {
			if ( in_array( $placement, $message['placement'], true ) ) {
				$matched[] = array(
					'message' => $message['message'],
					'type'    => $message['type'],
				);
			}
		}

		return $matched;
	}

	/**
	 * Run the event to fetch the data.
	 *
	 * @param ITSEC_Job $job
	 */
	public static function run_event( $job ) {
		$fetched = self::fetch();

		if ( is_wp_error( $fetched ) ) {
			$job->reschedule_in( 5 * MINUTE_IN_SECONDS );

			return;
		}
	}

	/**
	 * Fetches and stores the remote messages response.
	 *
	 * @return null|WP_Error
	 */
	public static function fetch() {
		$response = wp_remote_get( self::URL, array(
			'user-agent' => 'WordPress',
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$data = wp_remote_retrieve_body( $response );

		if ( ! $data ) {
			return new WP_Error( 'empty_body', __( 'Empty response body.', 'better-wp-security' ) );
		}

		$json = json_decode( $data, true );

		if ( ! $json ) {
			return new WP_Error( 'invalid_json', __( 'Invalid json response.', 'better-wp-security' ) );
		}

		$sanitized = self::sanitize_response( $json );

		update_site_option( self::OPTION, array(
			'response'  => $sanitized,
			'ttl'       => $sanitized['ttl'],
			'requested' => ITSEC_Core::get_current_time_gmt(),
		) );

		return null;
	}

	/**
	 * Sanitizes the JSON response.
	 *
	 * @param array $json
	 *
	 * @return array
	 */
	public static function sanitize_response( $json ) {
		$json = wp_parse_args( $json, array(
			'ttl'      => HOUR_IN_SECONDS,
			'messages' => array(),
			'actions'  => array(),
			'features' => array(),
		) );

		$sanitized = array(
			'ttl'      => absint( $json['ttl'] ),
			'messages' => array(),
			'features' => array(),
			'actions'  => wp_parse_slug_list( $json['actions'] ),
		);

		foreach ( $json['messages'] as $message ) {
			$sanitized['messages'][] = array(
				'message'   => self::sanitize_message( $message['message'] ),
				'type'      => self::sanitize_type( $message['type'] ),
				'placement' => $message['placement'],
			);
		}

		foreach ( $json['features'] as $feature => $f_config ) {
			$sanitized['features'][ $feature ] = [
				'rate'         => isset( $f_config['rate'] ) ? (int) $f_config['rate'] : false,
				'disabled'     => ! empty( $f_config['disabled'] ),
				'requirements' => isset( $f_config['requirements'] ) && is_array( $f_config['requirements'] ) ? $f_config['requirements'] : [],
			];
		}

		return $sanitized;
	}

	private static function sanitize_message( $message ) {
		return wp_kses( $message, array( 'a' => array( 'href' => true ) ) );
	}

	private static function sanitize_type( $type ) {
		if ( in_array( $type, array( 'success', 'info', 'warning', 'error' ), true ) ) {
			return $type;
		}

		return 'info';
	}

	private static function get_response() {

		if ( ! ITSEC_Core::is_pro() ) {
			return array();
		}

		if ( ! isset( self::$_response ) ) {
			self::$_response = self::load_response();
		}

		return apply_filters( 'itsec_remote_messages', self::$_response );
	}

	/**
	 * Loads the response from the local cache or the server.
	 *
	 * @return array|mixed
	 */
	private static function load_response() {
		$data = self::get_stored_response();

		if ( ! $data['response'] ) {
			self::schedule_check();

			return array();
		}

		if ( $data['requested'] + $data['ttl'] < ITSEC_Core::get_current_time_gmt() ) {
			self::schedule_check();
			$events = ITSEC_Core::get_scheduler()->get_single_events();

			foreach ( $events as $event ) {
				// If we are less than an hour late for processing the refresh, return the stale data.
				if ( self::EVENT === $event['id'] ) {
					if ( $event['fire_at'] + HOUR_IN_SECONDS > ITSEC_Core::get_current_time_gmt() ) {
						return $data['response'];
					}

					// If its been more than a day, call the API right now.
					if ( $event['fire_at'] + DAY_IN_SECONDS > ITSEC_Core::get_current_time_gmt() ) {
						ITSEC_Core::get_scheduler()->run_single_event( self::EVENT );
						$data = self::get_stored_response();

						if ( $data['requested'] === ITSEC_Core::get_current_time_gmt() ) {
							return $data['response'];
						}
					}
				}
			}

			return array();
		}

		return $data['response'];
	}

	private static function get_stored_response() {
		$data = get_site_option( self::OPTION, array() );
		$data = wp_parse_args( $data, array(
			'response'  => array(),
			'requested' => 0,
			'ttl'       => 0,
		) );

		return $data;
	}

	private static function schedule_check() {
		$s = ITSEC_Core::get_scheduler();

		if ( ! $s->is_single_scheduled( self::EVENT, null ) ) {
			$s->schedule_once( ITSEC_Core::get_current_time_gmt() + 60, self::EVENT );
		}
	}
}
