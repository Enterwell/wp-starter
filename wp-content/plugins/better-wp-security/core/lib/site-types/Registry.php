<?php

namespace iThemesSecurity\Lib\Site_Types;

final class Registry {

	/** @var Site_Type[] */
	private $site_types = [];

	/**
	 * Registers a site type.
	 *
	 * @param Site_Type $site_type
	 *
	 * @return $this
	 */
	public function register( Site_Type $site_type ): self {
		$this->site_types[] = $site_type;

		return $this;
	}

	/**
	 * Gets the list of registered site types.
	 *
	 * @return Site_Type[]
	 */
	public function get_site_types(): array {
		return $this->site_types;
	}

	/**
	 * Gets a Site Type object by it's slug.
	 *
	 * @param string $slug
	 *
	 * @return Site_Type|null
	 */
	public function get_by_slug( string $slug ) {
		foreach ( $this->get_site_types() as $site_type ) {
			if ( $slug === $site_type->get_slug() ) {
				return $site_type;
			}
		}

		return null;
	}
}
