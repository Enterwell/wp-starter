<?php

namespace iThemesSecurity;

final class Module_Config {

	const T_ABOUT = 'about';
	const T_SETTINGS = 'settings';
	const T_USER_GROUPS = 'user-groups';
	const T_PASSWORD_REQUIREMENTS = 'password-requirements';
	const T_TOOLS = 'tools';
	const T_ALL = [
		self::T_ABOUT,
		self::T_SETTINGS,
		self::T_USER_GROUPS,
		self::T_PASSWORD_REQUIREMENTS,
		self::T_TOOLS,
	];
	const TRANSLATE = [ 'title', 'description', 'help', 'keywords', 'enumNames' ];

	/** @var array */
	private $config;

	/** @var Module_Config|null */
	private $translated;

	/** @var string[] */
	private $translated_fields = [];

	/**
	 * Module_Config constructor.
	 *
	 * @param array $config
	 */
	public function __construct( array $config ) {
		$this->config = self::transform_module_config( $config );
	}

	/**
	 * Get's the ID.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return $this->get_config()['id'];
	}

	public function get_config(): array {
		return $this->config;
	}

	public function get_status( $for = '' ): string {
		$status = $this->get_config()['status'];

		if ( is_string( $status ) ) {
			return $status;
		}

		if ( ! $for ) {
			$for = \ITSEC_Core::is_pro() ? 'pro' : 'free';
		}

		return $status[ $for ];
	}

	public function get_type(): string {
		return $this->get_config()['type'];
	}

	public function is_deprecated(): bool {
		return ! empty( $this->get_config()['deprecated'] );
	}

	public function get_deprecated_version(): string {
		return $this->get_config()['deprecated'] ?? '';
	}

	public function get_order(): int {
		return $this->get_config()['order'] ?? 10;
	}

	public function is_onboard(): bool {
		return $this->get_config()['onboard'] ?? false;
	}

	public function has_side_effects(): bool {
		return ! empty( $this->get_config()['side-effects'] );
	}

	public function get_keywords(): array {
		return $this->get_config()['keywords'] ?? [];
	}

	public function get_title(): string {
		return $this->get_config()['title'] ?? '';
	}

	public function get_description(): string {
		return $this->get_config()['description'] ?? '';
	}

	public function get_help(): string {
		return $this->get_config()['help'] ?? '';
	}

	public function get_tools(): array {
		return $this->get_config()['tools'] ?? [];
	}

	public function get_user_groups(): array {
		return $this->get_config()['user-groups'] ?? [];
	}

	public function get_password_requirements(): array {
		return $this->get_config()['password-requirements'] ?? [];
	}

	public function get_settings(): array {
		return $this->get_config()['settings'] ?? [];
	}

	public function get_conditional_settings(): array {
		return $this->get_config()['conditional-settings'] ?? [];
	}

	public function get_removed_settings(): array {
		return $this->get_config()['removed-settings'] ?? [];
	}

	public function get_deprecated_settings(): array {
		return $this->get_config()['deprecated-settings'] ?? [];
	}

	public function get_onboard_settings(): array {
		return $this->get_config()['onboard-settings'] ?? [];
	}

	public function get_scheduling(): array {
		return $this->get_config()['scheduling'] ?? [];
	}

	public function get_extends(): string {
		return $this->get_config()['extends'] ?? '';
	}

	public function get_requirements(): array {
		return $this->get_config()['requirements'] ?? [];
	}

	public function get_feature_flags(): array {
		return $this->get_config()['feature-flags'] ?? [];
	}

	public function get_import_settings(): array {
		return $this->get_config()['import-export']['import-settings'] ?? [];
	}

	public function get_export_excluded_settings(): array {
		return $this->get_config()['import-export']['exclude-settings'] ?? [];
	}

	public function get_encrypted_user_meta_keys(): array {
		return $this->get_config()['encryption']['user-meta'] ?? [];
	}

	/**
	 * Translates user-facing strings and returns a new
	 * config instance with those translations applied.
	 *
	 * @param string ...$fields Optionally, limit to translating only the given fields.
	 *
	 * @return $this
	 */
	public function translate( string ...$fields ): self {
		$fields = $fields ?: self::T_ALL;

		if ( ! $this->translated ) {
			$this->translated = clone $this;
		}

		foreach ( $fields as $field ) {
			if ( in_array( $field, $this->translated->translated_fields, true ) ) {
				continue;
			}

			if ( 'about' === $field ) {
				$this->translated->config['title']       = static::translate_value( $this->get_title() );
				$this->translated->config['description'] = static::translate_value( $this->get_description() );
				$this->translated->config['help']        = static::translate_value( $this->get_help() );
				$this->translated->config['keywords']    = array_map( static function ( $value ) {
					return static::translate_value( $value );
				}, $this->get_keywords() );
			} elseif ( isset( $this->translated->config[ $field ] ) ) {
				self::apply_translate( $this->translated->config[ $field ] );
			}

			$this->translated->translated_fields[] = $field;
		}

		return $this->translated;
	}

	/**
	 * Extends the module config with a provided module config.
	 *
	 * @param Module_Config $with
	 *
	 * @return $this A new merged module config is returned.
	 */
	public function extend( Module_Config $with ): self {
		$new = clone $this;

		if ( $with->get_keywords() ) {
			$new->config['keywords'] = array_merge( $new->get_keywords(), $with->get_keywords() );
		}

		if ( $with->get_tools() ) {
			$new->config['tools'] = array_merge( $new->get_tools(), $with->get_tools() );
		}

		if ( $with->get_user_groups() ) {
			$new->config['user-groups'] = array_merge( $new->get_user_groups(), $with->get_user_groups() );
		}

		if ( $with->get_password_requirements() ) {
			$new->config['password-requirements'] = array_merge( $new->get_password_requirements(), $with->get_password_requirements() );
		}

		if ( $with->get_settings() ) {
			if ( ! $new->get_settings() ) {
				$new->config['settings'] = $with->get_settings();
			} else {
				$new->config['settings'] = \ITSEC_Lib::array_merge_recursive_distinct( $new->config['settings'], $with->get_settings() );
			}
		}

		if ( $with->get_conditional_settings() ) {
			$new->config['conditional-settings'] = array_merge( $new->get_conditional_settings(), $with->get_conditional_settings() );
		}

		if ( $with->get_removed_settings() ) {
			$new->config['removed-settings'] = array_merge( $new->get_removed_settings(), $with->get_removed_settings() );
		}

		if ( $with->get_removed_settings() ) {
			$new->config['removed-settings'] = array_merge( $new->get_removed_settings(), $with->get_removed_settings() );
		}

		if ( $with->get_deprecated_settings() ) {
			$new->config['deprecated-settings'] = array_merge( $new->get_deprecated_settings(), $with->get_deprecated_settings() );
		}

		if ( $with->get_onboard_settings() ) {
			$new->config['onboard-settings'] = array_merge( $new->get_onboard_settings(), $with->get_onboard_settings() );
		}

		if ( $with->get_scheduling() ) {
			$new->config['scheduling'] = array_merge( $new->get_scheduling(), $with->get_scheduling() );
		}

		return $new;
	}

	/**
	 * Extracts the list of strings that can be translated.
	 *
	 * @return array
	 */
	public function extract_translatable_strings(): array {
		$config  = $this->config;
		$strings = array_intersect_key( $config, array_flip( [
			'title',
			'description',
			'help',
		] ) );

		if ( isset( $config['keywords'] ) ) {
			$strings = array_merge( $strings, $config['keywords'] );
		}

		$extract = static function ( &$value ) use ( &$strings, &$extract ) {
			if ( ! is_array( $value ) ) {
				return;
			}

			foreach ( self::TRANSLATE as $field ) {
				if ( isset( $value[ $field ] ) ) {
					$strings = array_merge( $strings, (array) $value[ $field ] );
				}
			}

			array_walk( $value, $extract );
		};

		foreach ( self::T_ALL as $field ) {
			if ( self::T_ABOUT === $field ) {
				continue;
			}

			if ( isset( $config[ $field ] ) ) {
				$extract( $config[ $field ] );
			}
		}

		return array_filter( array_values( $strings ), function ( $string ) {
			return is_string( $string ) && trim( $string, " \t\n\r\0\x0B​" ) !== '';
		} );
	}

	/**
	 * Transforms a module's config definition.
	 *
	 * @param array $config The module config.
	 *
	 * @return array
	 */
	private static function transform_module_config( array $config ): array {
		$transformed = \ITSEC_Lib::resolve_schema_refs( $config );

		if ( isset( $transformed['user-groups'] ) ) {
			$item = [
				'oneOf' => [
					[
						'type'   => 'string',
						'format' => 'uuid',
					],
					[
						'type' => 'string',
						'enum' => [ 'everybody-else' ],
					]
				]
			];

			foreach ( $transformed['user-groups'] as $slug => $group ) {
				if ( isset( $transformed['settings']['properties'][ $slug ] ) ) {
					continue;
				}

				if ( $group['type'] === 'multiple' ) {
					$transformed['settings']['properties'][ $slug ] = [
						'type'     => 'array',
						'items'    => $item,
						'default'  => [],
						'readonly' => true,
					];
				} else {
					$transformed['settings']['properties'][ $slug ] = [
						'oneOf'    => $item['oneOf'],
						'default'  => '',
						'readonly' => true,
					];
				}
			}
		}

		if ( isset( $transformed['settings'] ) ) {
			if ( ! isset( $transformed['settings']['$schema'] ) ) {
				$transformed['settings']['$schema'] = 'http://json-schema.org/draft-04/schema#';
			}

			if ( ! isset( $transformed['settings']['id'] ) ) {
				$transformed['settings']['id'] = 'itsec-module-' . $config['id'];
			}
		}

		/**
		 * Filters the config definition for a module.
		 *
		 * @param array $transformed The transformed config.
		 * @param array $config      The original config.
		 */
		return apply_filters( "itsec_{$config['id']}_module_config", $transformed, $config );
	}

	/**
	 * Resolves $ref entries at any point in the config.
	 *
	 * Currently, only a simplified form of JSON Pointers are supported where `/` is the only
	 * allowed control character.
	 *
	 * Additionally, the `$ref` keyword must start with `#/definitions`.
	 *
	 * @param mixed  $value       The incoming value.
	 * @param string $key         The array key.
	 * @param array  $definitions The shared definitions.
	 */
	private static function resolve_ref( &$value, $key, $definitions ) {
		if ( ! is_array( $value ) ) {
			return;
		}

		if ( isset( $value['$ref'] ) ) {
			$ref   = str_replace( '#/definitions/', '', $value['$ref'] );
			$value = \ITSEC_Lib::array_get( $definitions, $ref, null, '/' );

			return;
		}

		array_walk( $value, [ static::class, 'resolve_ref' ], $definitions );
	}

	/**
	 * Applies translations to the given value.
	 *
	 * @param mixed $value
	 */
	private static function apply_translate( &$value ) {
		if ( ! is_array( $value ) ) {
			return;
		}

		foreach ( self::TRANSLATE as $field ) {
			if ( isset( $value[ $field ] ) ) {
				if ( is_string( $value[ $field ] ) ) {
					$value[ $field ] = static::translate_value( $value[ $field ] );
				} elseif ( wp_is_numeric_array( $value[ $field ] ) ) {
					$value[ $field ] = array_map( [ static::class, 'translate_value' ], $value[ $field ] );
				}
			}
		}

		array_walk( $value, [ static::class, 'apply_translate' ] );
	}

	private static function translate_value( string $value ): string {
		$translated = translate( $value, 'better-wp-security' );

		if ( strpos( $value, '[' ) !== false ) {
			$translated = preg_replace_callback( '/\[([^\]]+)\]\(([^\)]+)\)/', function ( $matches ) {
				if ( 0 === strpos( $matches[2], 'itsec://' ) ) {
					$path = substr( $matches[2], strlen( 'itsec://' ) );

					return \ITSEC_Core::get_link_for_settings_route( '/' . $path ) . $matches[1] . '</a>';
				}

				return sprintf( '<a href="%s">%s</a>', esc_url( $matches[2] ), $matches[1] );
			}, $translated );
		}

		return $translated;
	}
}
