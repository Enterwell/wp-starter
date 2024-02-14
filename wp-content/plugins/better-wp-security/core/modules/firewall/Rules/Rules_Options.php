<?php

namespace iThemesSecurity\Modules\Firewall\Rules;

class Rules_Options {

	/** @var array */
	private $for_refs = [];

	private $search = '';

	private $providers = [];

	private $vulnerabilities = [];

	/** @var bool|null */
	private $paused;

	/** @var int */
	private $page;

	/** @var int */
	private $per_page;

	/**
	 * Looks for a specific rule from a provider.
	 *
	 * @param string $provider The provider slug.
	 * @param string $ref      The provider's reference to find it by.
	 *
	 * @return $this
	 */
	public function for_provider_ref( string $provider, string $ref ): self {
		$this->for_refs[] = compact( 'provider', 'ref' );

		return $this;
	}

	public function get_provider_refs(): array {
		return $this->for_refs;
	}

	public function get_search(): string {
		return $this->search;
	}

	public function set_search( string $search ): self {
		$this->search = $search;

		return $this;
	}

	public function get_providers(): array {
		return $this->providers;
	}

	public function set_providers( array $providers ): self {
		$this->providers = $providers;

		return $this;
	}

	public function get_vulnerabilities(): array {
		return $this->vulnerabilities;
	}

	public function set_vulnerabilities( array $vulnerabilities ): self {
		$this->vulnerabilities = $vulnerabilities;

		return $this;
	}

	public function get_paused(): ?bool {
		return $this->paused;
	}

	public function set_paused( ?bool $paused ): self {
		$this->paused = $paused;

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
