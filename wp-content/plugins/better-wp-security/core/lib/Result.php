<?php

namespace iThemesSecurity\Lib;

/**
 * @template T
 */
final class Result {
	const SUCCESS = 'success';
	const ERROR = 'error';

	/** @var string */
	private $type;

	/** @var \WP_Error */
	private $error;

	/** @var T */
	private $data;

	private $success = [];

	private $info = [];

	private $warning = [];

	private function __construct( string $type ) {
		$this->type = $type;
	}

	/**
	 * Create a "success" result.
	 *
	 * @param T $data
	 *
	 * @return static
	 */
	public static function success( $data = null ): self {
		$self       = new self( self::SUCCESS );
		$self->data = $data;

		return $self;
	}

	public static function error( \WP_Error $error ): self {
		$self        = new self( self::ERROR );
		$self->error = $error;

		return $self;
	}

	public static function combine( ?Result ...$results ): self {
		return self::combine_with_success_data( null, ...array_filter( $results ) );
	}

	/**
	 * Combine multiple result objects with data to use as the success value when applicable.
	 *
	 * @param T      $data
	 * @param Result ...$results
	 *
	 * @return static
	 */
	public static function combine_with_success_data( $data, Result ...$results ): self {
		$error   = new \WP_Error();
		$warning = [];
		$info    = [];
		$success = [];

		foreach ( $results as $result ) {
			if ( ! $result->is_success() ) {
				$error->merge_from( $result->get_error() );
			}

			$warning[] = $result->get_warning_messages();
			$info[]    = $result->get_info_messages();
			$success[] = $result->get_success_messages();
		}

		if ( $error->has_errors() ) {
			$result = self::error( $error );
		} else {
			$result = self::success( $data );
		}

		if ( $warning ) {
			$result->add_warning_message( ...array_merge( ...$warning ) );
		}

		if ( $info ) {
			$result->add_info_message( ...array_merge( ...$info ) );
		}

		if ( $success ) {
			$result->add_success_message( ...array_merge( ...$success ) );
		}

		return $result;
	}

	public static function from_response(): self {
		if ( \ITSEC_Response::is_success() ) {
			$result = self::success( \ITSEC_Response::get_response() );
		} else {
			$result = self::error( \ITSEC_Response::as_wp_error() );
		}

		$result->add_warning_message( ...\ITSEC_Response::get_warnings() );
		$result->add_info_message( ...\ITSEC_Response::get_infos() );
		$result->add_success_message( ...\ITSEC_Response::get_messages() );

		return $result;
	}

	public function is_success(): bool { return self::SUCCESS === $this->type; }

	/**
	 * Returns the Result data.
	 *
	 * @return T
	 */
	public function get_data() { return $this->data; }

	public function get_error(): \WP_Error { return $this->error; }

	public function add_success_message( string ...$messages ): self {
		$this->success = array_merge( $this->success, $messages );

		return $this;
	}

	public function get_success_messages(): array { return $this->success; }

	public function add_info_message( string ...$messages ): self {
		$this->info = array_merge( $this->info, $messages );

		return $this;
	}

	public function get_info_messages(): array { return $this->info; }

	public function add_warning_message( string ...$messages ): self {
		$this->warning = array_merge( $this->warning, $messages );

		return $this;
	}

	public function get_warning_messages(): array { return $this->warning; }

	public function as_rest_response(): \WP_REST_Response {
		if ( $this->is_success() ) {
			$data = $this->get_data();

			if ( $data instanceof \JsonSerializable ) {
				$data = $data->jsonSerialize();
			}

			$response = new \WP_REST_Response( $data, $data ? 200 : 204 );
		} else {
			$response = \ITSEC_Lib_REST::error_to_response( $this->get_error() );
		}

		if ( $success = $this->get_success_messages() ) {
			$response->header( 'X-Messages-Success', wp_json_encode( $success ) );
		}

		if ( $info = $this->get_info_messages() ) {
			$response->header( 'X-Messages-Info', wp_json_encode( $info ) );
		}

		if ( $warning = $this->get_warning_messages() ) {
			$response->header( 'X-Messages-Warning', wp_json_encode( $warning ) );
		}

		return $response;
	}

	public function for_wp_cli() {
		foreach ( $this->get_warning_messages() as $message ) {
			\WP_CLI::warning( $message );
		}

		foreach ( $this->get_info_messages() as $message ) {
			\WP_CLI::log( $message );
		}

		foreach ( $this->get_success_messages() as $message ) {
			\WP_CLI::success( $message );
		}

		if ( ! $this->is_success() ) {
			\WP_CLI::error( $this->get_error() );
		}
	}
}
