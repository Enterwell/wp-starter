<?php

namespace iThemesSecurity\Site_Scanner\Repository;

use iThemesSecurity\Site_Scanner\Vulnerability;

final class Vulnerabilities_Options {

	/** @var string[] */
	private $resolutions = [];

	/** @var string[] */
	private $types = [];

	/** @var array[] */
	private $software = [];

	/** @var \DateTimeInterface */
	private $first_seen_after;

	/** @var \DateTimeInterface */
	private $first_seen_before;

	/** @var \DateTimeInterface */
	private $last_seen_after;

	/** @var \DateTimeInterface */
	private $last_seen_before;


	/** @var int */
	private $page;

	/** @var int */
	private $per_page;

	public function get_resolutions(): array {
		return $this->resolutions;
	}

	public function set_resolutions( array $resolutions ): self {
		$this->resolutions = $resolutions;

		return $this;
	}

	public function get_types(): array {
		return $this->types;
	}

	public function set_types( array $types ): self {
		$this->types = $types;

		return $this;
	}

	public function get_first_seen_before(): ?\DateTimeInterface {
		return $this->first_seen_before;
	}

	public function set_first_seen_before( \DateTimeImmutable $first_seen_before ): self {
		$this->first_seen_before = $first_seen_before;

		return $this;
	}

	public function get_first_seen_after(): ?\DateTimeInterface {
		return $this->first_seen_after;
	}

	public function set_first_seen_after( \DateTimeImmutable $first_seen_after ): self {
		$this->first_seen_after = $first_seen_after;

		return $this;
	}

	public function get_last_seen_before(): ?\DateTimeInterface {
		return $this->last_seen_before;
	}

	public function set_last_seen_before( \DateTimeImmutable $last_seen_before ): self {
		$this->last_seen_before = $last_seen_before;

		return $this;
	}

	public function get_last_seen_after(): ?\DateTimeInterface {
		return $this->last_seen_after;
	}

	public function set_last_seen_after( \DateTimeImmutable $last_seen_after ): self {
		$this->last_seen_after = $last_seen_after;

		return $this;
	}

	public function get_software(): array {
		return $this->software;
	}

	public function add_software( string $type, string $slug = '' ): self {
		if ( ! in_array( $type, [
			Vulnerability::T_WORDPRESS,
			Vulnerability::T_PLUGIN,
			Vulnerability::T_THEME
		], true ) ) {
			throw new \InvalidArgumentException( 'Invalid software type.' );
		}

		if ( $type === Vulnerability::T_WORDPRESS && $slug ) {
			throw new \InvalidArgumentException( 'Slug not allowed when querying for WordPress vulnerabilities.' );
		}

		if ( $type !== Vulnerability::T_WORDPRESS && ! $slug ) {
			throw new \InvalidArgumentException( 'Slug required when querying Plugin or Theme vulnerabilities.' );
		}

		$this->software[] = compact( 'type', 'slug' );

		return $this;
	}

	public function paginate( int $per_page = 10, int $page = 1 ): self {
		if ( $page < 1 ) {
			throw new \InvalidArgumentException( 'Page must be greater than 0.' );
		}

		if ( $per_page < 1 || $per_page > 100 ) {
			throw new \InvalidArgumentException( 'Per page must be greater than 0 and less than 100.' );
		}

		$this->per_page = $per_page;
		$this->page     = $page;

		return $this;
	}

	public function get_page(): ?int {
		return $this->page;
	}

	public function get_per_page(): ?int {
		return $this->per_page;
	}
}
