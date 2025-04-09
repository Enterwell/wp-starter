<?php

namespace iThemesSecurity\Site_Scanner;

class Vulnerability {
	public const T_WORDPRESS = 'wordpress';
	public const T_PLUGIN = 'plugin';
	public const T_THEME = 'theme';

	public const R_PATCHED = 'patched';
	public const R_AUTO_UPDATED = 'auto-updated';
	public const R_UPDATED = 'updated';
	public const R_DEACTIVATED = 'deactivated';
	public const R_DELETED = 'deleted';
	public const R_MUTED = 'muted';

	/** @var string */
	private $id;

	/** @var string */
	private $software_type;

	/** @var string */
	private $software_slug;

	/** @var array */
	private $details;

	/** @var \DateTimeInterface|null */
	private $first_seen;

	/** @var \DateTimeInterface|null */
	private $last_seen;

	/** @var \DateTimeInterface|null */
	private $resolved_at;

	/** @var \WP_User|null */
	private $resolved_by;

	/** @var string */
	private $resolution;

	public function __construct(
		string $id,
		string $software_type,
		string $software_slug,
		array $details,
		?\DateTimeInterface $first_seen = null,
		?\DateTimeInterface $last_seen = null,
		?\DateTimeInterface $resolved_at = null,
		?\WP_User $resolved_by = null,
		string $resolution = ''
	) {
		if ( ! in_array( $software_type, [ self::T_PLUGIN, self::T_THEME, self::T_WORDPRESS ], true ) ) {
			throw new \InvalidArgumentException( 'Invalid product type.' );
		}

		if (
			! in_array( $resolution, [
				'',
				self::R_PATCHED,
				self::R_AUTO_UPDATED,
				self::R_UPDATED,
				self::R_DEACTIVATED,
				self::R_DELETED,
				self::R_MUTED
			], true )
		) {
			throw new \InvalidArgumentException( 'Invalid resolution.' );
		}

		$this->id            = $id;
		$this->software_type = $software_type;
		$this->software_slug = $software_slug;
		$this->details       = $details;
		$this->first_seen    = $first_seen;
		$this->last_seen     = $last_seen;
		$this->resolved_at   = $resolved_at;
		$this->resolved_by   = $resolved_by;
		$this->resolution    = $resolution;
	}

	public function seen(): void {
		if ( ! $this->first_seen ) {
			$this->first_seen = \ITSEC_Core::get_current_time_gmt( true );
		}

		$this->last_seen = \ITSEC_Core::get_current_time_gmt( true );
	}

	private function resolve( \WP_User $by = null ): void {
		$this->seen();
		$this->resolved_at = \ITSEC_Core::get_current_time_gmt( true );
		$this->resolved_by = $by;
	}

	public function patched(): self {
		if ( $this->is_patched() ) {
			return $this;
		}

		$this->resolve();
		$this->resolution = self::R_PATCHED;

		return $this;
	}

	public function updated( bool $automatic, \WP_User $by = null ): self {
		if ( $this->is_updated() ) {
			return $this;
		}

		$this->resolve( $by );
		$this->resolution = $automatic ? self::R_AUTO_UPDATED : self::R_UPDATED;

		return $this;
	}

	public function deactivated( \WP_User $by = null ): self {
		if ( $this->is_deactivated() ) {
			return $this;
		}

		$this->resolve( $by );
		$this->resolution = self::R_DEACTIVATED;

		return $this;
	}

	public function deleted( \WP_User $by = null ): self {
		if ( $this->is_deleted() ) {
			return $this;
		}

		$this->resolve( $by );
		$this->resolution = self::R_DELETED;

		return $this;
	}

	public function muted( \WP_User $by = null ): self {
		if ( $this->is_muted() ) {
			return $this;
		}

		$this->resolve( $by );
		$this->resolution = self::R_MUTED;

		return $this;
	}

	public function unresolve(): self {
		$this->resolution  = '';
		$this->resolved_by = null;
		$this->resolved_at = null;

		return $this;
	}

	public function unmute(): self {
		if ( $this->resolution === self::R_MUTED ) {
			if ( ! $this->is_software_installed() ) {
				$this->deleted();
			} elseif ( ! $this->is_software_active() ) {
				$this->deactivated();
			} else {
				$this->unresolve();
			}
		}

		return $this;
	}

	public function get_id(): string {
		return $this->id;
	}

	public function get_software_type(): string {
		return $this->software_type;
	}

	public function get_software_type_label(): string {
		switch ( $this->get_software_type() ) {
			case self::T_WORDPRESS:
				return __( 'WordPress', 'better-wp-security' );
			case self::T_PLUGIN:
				return __( 'Plugin', 'better-wp-security' );
			case self::T_THEME:
				return __( 'Theme', 'better-wp-security' );
			default:
				return '';
		}
	}

	public function get_software_slug(): string {
		return $this->software_slug;
	}

	public function get_details(): array {
		return $this->details;
	}

	public function get_first_seen(): ?\DateTimeInterface {
		return $this->first_seen;
	}

	public function get_last_seen(): ?\DateTimeInterface {
		return $this->last_seen;
	}

	public function get_resolved_at(): ?\DateTimeInterface {
		return $this->resolved_at;
	}

	public function get_resolved_by(): ?\WP_User {
		return $this->resolved_by;
	}

	public function get_resolution(): string {
		return $this->resolution;
	}

	public function get_resolution_label(): string {
		switch ( $this->get_resolution() ) {
			case self::R_PATCHED:
				return __( 'Mitigated', 'better-wp-security' );
			case self::R_AUTO_UPDATED:
				return __( 'Auto-Updated', 'better-wp-security' );
			case self::R_UPDATED:
				return __( 'Updated', 'better-wp-security' );
			case self::R_DEACTIVATED:
				if ( $this->get_first_seen() == $this->get_resolved_at() ) {
					return __( 'Inactive', 'better-wp-security' );
				}

				return __( 'Deactivated', 'better-wp-security' );
			case self::R_DELETED:
				return __( 'Deleted', 'better-wp-security' );
			case self::R_MUTED:
				return __( 'Muted', 'better-wp-security' );
			default:
				return __( 'Unresolved', 'better-wp-security' );
		}
	}

	public function get_resolution_description(): string {
		if ( ! $this->is_resolved() ) {
			return '';
		}

		$date = wp_date(
			get_option( 'date_format' ),
			$this->get_resolved_at()->getTimestamp()
		);
		$time = wp_date(
			get_option( 'time_format' ),
			$this->get_resolved_at()->getTimestamp()
		);

		switch ( $this->get_resolution() ) {
			case self::R_PATCHED:
				return __( 'A virtual patch was automatically applied to mitigate this vulnerability.', 'better-wp-security' );
			case self::R_MUTED:
				if ( $this->get_resolved_by() ) {
					$by = $this->get_resolved_by()->display_name;

					return sprintf(
					/* translators: 1. Name, 2. Formatted date, 3. Formatted time. */
						__( 'This vulnerability was muted by %1$s on %2$s at %3$s.', 'better-wp-security' ),
						$by,
						$date,
						$time
					);
				} else {
					/* translators: 1. Formatted date, 2. Formatted time. */
					return sprintf( __( 'This vulnerability was muted on %1$s at %2$s.', 'better-wp-security' ), $date, $time );
				}
			case self::R_AUTO_UPDATED:
				switch ( $this->get_software_type() ) {
					case self::T_PLUGIN:
						return sprintf(
						/* translators: 1. Formatted date, 2. Formatted time. */
							__( 'The plugin was automatically updated on %1$s at %2$s.', 'better-wp-security' ),
							$date,
							$time
						);
					case self::T_THEME:
						return sprintf(
						/* translators: 1. Formatted date, 2. Formatted time. */
							__( 'The theme was automatically updated on %1$s at %2$s.', 'better-wp-security' ),
							$date,
							$time
						);
					case self::T_WORDPRESS:
						return sprintf(
						/* translators: 1. Formatted date, 2. Formatted time. */
							__( 'WordPress Core was automatically updated on %1$s at %2$s.', 'better-wp-security' ),
							$date,
							$time
						);
					default:
						return '';
				}
			case self::R_UPDATED:
				if ( $this->get_resolved_by() ) {
					$by = $this->get_resolved_by()->display_name;

					switch ( $this->get_software_type() ) {
						case self::T_PLUGIN:
							return sprintf(
							/* translators: 1. Name, 2. Formatted date, 3. Formatted time. */
								__( 'The plugin was updated by %1$s on %2$s at %3$s.', 'better-wp-security' ),
								$by,
								$date,
								$time
							);
						case self::T_THEME:
							return sprintf(
							/* translators: 1. Name, 2. Formatted date, 3. Formatted time. */
								__( 'The theme was updated by %1$s on %2$s at %3$s.', 'better-wp-security' ),
								$by,
								$date,
								$time
							);
						case self::T_WORDPRESS:
							return sprintf(
							/* translators: 1. Name, 2. Formatted date, 3. Formatted time. */
								__( 'WordPress Core was updated by %1$s on %2$s at %3$s.', 'better-wp-security' ),
								$by,
								$date,
								$time
							);
						default:
							return '';
					}
				} else {
					switch ( $this->get_software_type() ) {
						case self::T_PLUGIN:
							return sprintf(
							/* translators: 1. Formatted date, 2. Formatted time. */
								__( 'The plugin was updated on %1$s at %2$s.', 'better-wp-security' ),
								$date,
								$time
							);
						case self::T_THEME:
							return sprintf(
							/* translators: 1. Formatted date, 2. Formatted time. */
								__( 'The theme was updated on %s1$ at %2$s.', 'better-wp-security' ),
								$date,
								$time
							);
						case self::T_WORDPRESS:
							return sprintf(
							/* translators: 1. Formatted date, 2. Formatted time. */
								__( 'WordPress Core was updated on %1$s at %2$s.', 'better-wp-security' ),
								$date,
								$time
							);
						default:
							return '';
					}
				}
			case self::R_DEACTIVATED:
				if ( $this->get_first_seen() == $this->get_resolved_at() ) {
					switch ( $this->get_software_type() ) {
						case self::T_PLUGIN:
							return sprintf(
							/* translators: 1. Formatted date, 2. Formatted time */
								__( 'The plugin was inactive when scanned on %1$s at %2$s.', 'better-wp-security' ),
								$date,
								$time
							);
						case self::T_THEME:
							return sprintf(
							/* translators: 1. Formatted date, 2. Formatted time */
								__( 'The theme was inactive when scanned on %1$s at %2$s.', 'better-wp-security' ),
								$date,
								$time
							);
						default:
							return '';
					}
				} elseif ( $this->get_resolved_by() ) {
					$by = $this->get_resolved_by()->display_name;

					switch ( $this->get_software_type() ) {
						case self::T_PLUGIN:
							return sprintf(
							/* translators: 1. Name, 2. Formatted date, 3. Formatted time */
								__( 'The plugin was deactivated by %1$s on %2$s at %3$s.', 'better-wp-security' ),
								$by,
								$date,
								$time
							);
						case self::T_THEME:
							return sprintf(
							/* translators: 1. Name, 2. Formatted date, 3. Formatted time */
								__( 'The theme was deactivated by %1$s on %2$s at %3$s.', 'better-wp-security' ),
								$by,
								$date,
								$time
							);
						default:
							return '';
					}
				} else {
					switch ( $this->get_software_type() ) {
						case self::T_PLUGIN:
							return sprintf(
							/* translators: 1. Formatted date, 2. Formatted time */
								__( 'The plugin was deactivated on %1$s at %2$s.', 'better-wp-security' ),
								$date,
								$time
							);
						case self::T_THEME:
							return sprintf(
							/* translators: 1. Formatted date, 2. Formatted time */
								__( 'The theme was deactivated on %1$s at %2$s.', 'better-wp-security' ),
								$date,
								$time
							);
						default:
							return '';
					}
				}
			case self::R_DELETED:
				if ( $this->get_resolved_by() ) {
					$by = $this->get_resolved_by()->display_name;

					switch ( $this->get_software_type() ) {
						case self::T_PLUGIN:
							return sprintf(
							/* translators: 1. Name, 2. Formatted date, 3. Formatted time. */
								__( 'The plugin was deleted by %1$s on %2$s at %3$s.', 'better-wp-security' ),
								$by,
								$date,
								$time
							);
						case self::T_THEME:
							return sprintf(
							/* translators: 1. Name, 2. Formatted date, 3. Formatted time. */
								__( 'The theme was deleted by %1$s on %2$s at %3$s.', 'better-wp-security' ),
								$by,
								$date,
								$time
							);
						default:
							return '';
					}
				} else {
					switch ( $this->get_software_type() ) {
						case self::T_PLUGIN:
							return sprintf(
							/* translators: 1. Formatted date, 2. Formatted time. */
								__( 'The plugin was deleted on %1$s at %2$s.', 'better-wp-security' ),
								$date,
								$time
							);
						case self::T_THEME:
							return sprintf(
							/* translators: 1. Formatted date, 2. Formatted time. */
								__( 'The theme was deleted on %s1$ at %2$s.', 'better-wp-security' ),
								$date,
								$time
							);
						default:
							return '';
					}
				}
			default:
				return '';
		}
	}

	public function is_resolved(): bool {
		return $this->get_resolution() !== '';
	}

	public function is_patched(): bool {
		return $this->get_resolution() === self::R_PATCHED;
	}

	public function is_auto_updated(): bool {
		return $this->get_resolution() === self::R_AUTO_UPDATED;
	}

	public function is_manually_updated(): bool {
		return $this->get_resolution() === self::R_UPDATED;
	}

	public function is_updated(): bool {
		return $this->get_resolution() === self::R_UPDATED || $this->get_resolution() === self::R_AUTO_UPDATED;
	}

	public function is_deactivated(): bool {
		return $this->get_resolution() === self::R_DEACTIVATED;
	}

	public function is_deleted(): bool {
		return $this->get_resolution() === self::R_DELETED;
	}

	public function is_muted(): bool {
		return $this->get_resolution() === self::R_MUTED;
	}

	/**
	 * Checks if the vulnerable software is installed.
	 *
	 * @return bool
	 */
	public function is_software_installed(): bool {
		switch ( $this->get_software_type() ) {
			case self::T_WORDPRESS:
				return true;
			case self::T_PLUGIN:
				return (bool) $this->get_plugin_file();
			case self::T_THEME:
				return wp_get_theme( $this->get_software_slug() )->exists();
			default:
				return false;
		}
	}

	/**
	 * Checks if the vulnerable software is installed.
	 *
	 * @return bool
	 */
	public function is_software_active(): bool {
		switch ( $this->get_software_type() ) {
			case self::T_WORDPRESS:
				return true;
			case self::T_PLUGIN:
				$file = $this->get_plugin_file();

				return $file && is_plugin_active( $file );
			case self::T_THEME:
				return get_stylesheet() === $this->get_software_slug()
				       || get_template() === $this->get_software_slug();
			default:
				return false;
		}
	}

	/**
	 * Gets the main plugin file for the vulnerability.
	 *
	 * @return string|null
	 */
	public function get_plugin_file(): ?string {
		if ( $this->get_software_type() !== self::T_PLUGIN ) {
			return null;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		foreach ( get_plugins() as $file => $plugin ) {
			if ( str_starts_with( $file, $this->get_software_slug() . '/' ) ) {
				return $file;
			}
		}

		return null;
	}

	public function get_software_label(): string {
		switch ( $this->get_software_type() ) {
			case self::T_PLUGIN:
				if ( ! $file = $this->get_plugin_file() ) {
					return '';
				}

				$plugins = get_plugins();

				return $plugins[ $file ]['Name'] ?? '';
			case self::T_THEME:
				$theme = wp_get_theme( $this->get_software_slug() );

				return $theme->display( 'Name', false ) ?: '';
			default:
				return '';
		}
	}

	public function as_issue(): Vulnerability_Issue {
		return new Vulnerability_Issue(
			[
				'type'     => $this->get_software_type(),
				'software' => [
					'slug'           => $this->get_software_slug(),
					'label'          => '',
					'latest_version' => null,
				],
				'issues'   => [ $this->get_details() ],
			],
			$this->get_id(),
			''
		);
	}
}
