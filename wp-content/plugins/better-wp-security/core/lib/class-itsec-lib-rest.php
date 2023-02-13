<?php

class ITSEC_Lib_REST {
	const LINK_REL = 'https://s.api.ithemes.com/l/ithemes-security/';
	const DATE_FORMAT = 'Y-m-d\TH:i:sP';

	/**
	 * Get the URI for an iThemes Security link relation.
	 *
	 * @param string $relation
	 *
	 * @return string
	 */
	public static function get_link_relation( $relation ) {
		return self::LINK_REL . $relation;
	}

	/**
	 * Converts an error to a response object.
	 *
	 * This iterates over all error codes and messages to change it into a flat
	 * array. This enables simpler client behaviour, as it is represented as a
	 * list in JSON rather than an object/map.
	 *
	 * {@see WP_REST_Server::error_to_response()}
	 *
	 * @param WP_Error $error WP_Error instance.
	 *
	 * @return WP_REST_Response List of associative arrays with code and message keys.
	 */
	public static function error_to_response( WP_Error $error ) {
		$error_data = $error->get_error_data();

		if ( is_array( $error_data ) && isset( $error_data['status'] ) ) {
			$status = $error_data['status'];
		} else {
			$status = 500;
		}

		$errors = array();

		foreach ( (array) $error->errors as $code => $messages ) {
			foreach ( (array) $messages as $message ) {
				$errors[] = array(
					'code'    => $code,
					'message' => $message,
					'data'    => $error->get_error_data( $code ),
				);
			}
		}

		$data = $errors[0];
		if ( count( $errors ) > 1 ) {
			// Remove the primary error.
			array_shift( $errors );
			$data['additional_errors'] = $errors;
		}

		return new WP_REST_Response( $data, $status );
	}

	/**
	 * Get the status code to send from the list of statuses.
	 *
	 * @param int ...$statuses
	 *
	 * @return int
	 */
	public static function get_status( ...$statuses ) {
		if ( ! $statuses ) {
			return 200;
		}

		$prev = $statuses[0];

		foreach ( $statuses as $status ) {
			if ( $prev !== $status ) {
				return WP_Http::MULTI_STATUS;
			}

			$prev = $status;
		}

		return $prev;
	}

	/**
	 * Gets a Bearer token from the Authorization header.
	 *
	 * @param string $header
	 *
	 * @return string
	 */
	public static function get_token_from_auth_header( $header ) {
		$prefix = 'Bearer ';

		if ( 0 !== strpos( $header, $prefix ) ) {
			return '';
		}

		return trim( substr( $header, strlen( $prefix ) ) );
	}

	/**
	 * Gets the authorization status code to use.
	 *
	 * @param WP_User|null $user
	 *
	 * @return int
	 */
	public static function auth_code_required( $user = null ) {
		if ( func_num_args() === 0 ) {
			return rest_authorization_required_code();
		}

		return $user instanceof WP_User && $user->exists() ? 403 : 401;
	}
}
