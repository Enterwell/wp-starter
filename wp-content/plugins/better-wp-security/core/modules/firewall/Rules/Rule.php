<?php

namespace iThemesSecurity\Modules\Firewall\Rules;

class Rule {
	/** @var int */
	private $id;

	/** @var string */
	private $provider;

	/** @var string */
	private $provider_ref;

	/** @var string */
	private $name;

	/** @var string */
	private $vulnerability;

	/** @var array */
	private $config;

	/** @var \DateTimeInterface */
	private $created_at;

	/** @var \DateTimeInterface|null */
	private $paused_at;

	public function __construct(
		int $id,
		string $provider,
		string $provider_ref,
		string $name,
		string $vulnerability,
		array $config,
		\DateTimeInterface $created_at,
		?\DateTimeInterface $paused_at
	) {
		$this->id            = $id;
		$this->provider      = $provider;
		$this->provider_ref  = $provider_ref;
		$this->name          = $name;
		$this->vulnerability = $vulnerability;
		$this->config        = $config;
		$this->created_at    = $created_at;
		$this->paused_at     = $paused_at;
	}

	public static function create(
		string $provider,
		string $provider_ref,
		string $name,
		string $vulnerability,
		array $config
	): self {
		return new self(
			0,
			$provider,
			$provider_ref,
			$name,
			$vulnerability,
			$config,
			\ITSEC_Core::get_current_time_gmt( true ),
			null,
		);
	}

	public function get_id(): int {
		return $this->id;
	}

	public function get_provider(): string {
		return $this->provider;
	}

	public function get_provider_ref(): string {
		return $this->provider_ref;
	}

	public function get_vulnerability(): string {
		return $this->vulnerability;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function set_name( string $name ): self {
		$this->name = $name;

		return $this;
	}

	public function get_created_at(): \DateTimeInterface {
		return $this->created_at;
	}

	public function get_paused_at(): ?\DateTimeInterface {
		return $this->paused_at;
	}

	public function is_paused(): bool {
		return $this->paused_at !== null;
	}

	public function pause(): self {
		$this->paused_at = \ITSEC_Core::get_current_time_gmt( true );

		return $this;
	}

	public function resume(): self {
		$this->paused_at = null;

		return $this;
	}

	public function get_config(): array {
		return $this->config;
	}

	public function set_config( array $config ): self {
		$this->config = $config;

		return $this;
	}
}
