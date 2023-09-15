<?php

namespace iThemesSecurity\Lib;

final class Legacy_Password_Requirement implements Password_Requirement, \ArrayAccess, \IteratorAggregate {

	/** @var string */
	private $code;

	/** @var array */
	private $config;

	/**
	 * Legacy_Password_Requirement constructor.
	 *
	 * @param string $code
	 * @param array  $config
	 */
	public function __construct( string $code, array $config ) {
		$this->code   = $code;
		$this->config = $config;
	}

	public function get_code(): string {
		return $this->code;
	}

	public function get_module(): string {
		return '';
	}

	public function get_title(): string {
		if ( ! $this->config['settings_config'] ) {
			return $this->get_code();
		}

		return call_user_func( $this->config['settings_config'] )['label'] ?? $this->get_code();
	}

	public function get_description(): string {
		if ( ! $this->config['settings_config'] ) {
			return '';
		}

		return call_user_func( $this->config['settings_config'] )['description'];
	}

	public function is_password_change_required( \WP_User $user, array $settings ): bool {
		if ( ! $this->config['flag_check'] ) {
			return false;
		}

		return call_user_func( $this->config['flag_check'], $user, $settings );
	}

	public function evaluate( string $password, $user ) {
		if ( ! $this->config['evaluate'] ) {
			return new \WP_Error( 'not_implemented', __( 'This password requirement does not evaluate passwords.', 'better-wp-security' ) );
		}

		return call_user_func( $this->config['evaluate'], $password, $user );
	}

	public function validate( $evaluation, $user, array $settings, array $args ) {
		if ( ! $this->config['validate'] ) {
			return true;
		}

		return call_user_func( $this->config['validate'], $evaluation, $user, $settings, $args );
	}

	public function get_reason_message( $evaluation, array $settings ): string {
		return call_user_func( $this->config['reason'], $evaluation, $settings );
	}

	public function get_meta_key(): string {
		return $this->config['meta'] ?: "_itsec_password_evaluation_{$this->get_code()}";
	}

	public function is_always_enabled(): bool {
		return empty( $this->config['settings_config'] );
	}

	public function should_evaluate_if_not_enabled(): bool {
		return ! empty( $this->config['evaluate_if_not_enabled'] );
	}

	public function get_settings_schema(): array {
		if ( ! isset( $this->config['defaults'] ) ) {
			return [];
		}

		$schema = [
			'type'       => 'object',
			'properties' => [],
		];

		foreach ( $this->config['defaults'] as $setting => $default ) {
			$schema['properties'][ $setting ] = [
				'type'    => [ 'string', 'array' ],
				'default' => $default,
			];
		}

		return $schema;
	}

	public function has_user_group(): bool {
		return false;
	}

	public function getIterator(): \Traversable {
		return new \ArrayIterator( $this->config );
	}

	public function offsetExists( $offset ): bool {
		return isset( $this->config[ $offset ] );
	}

	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return $this->config[ $offset ] ?? null;
	}

	public function offsetSet( $offset, $value ): void {

	}

	public function offsetUnset( $offset ): void {

	}
}
