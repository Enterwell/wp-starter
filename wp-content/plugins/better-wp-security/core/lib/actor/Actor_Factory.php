<?php

namespace iThemesSecurity\Actor;

interface Actor_Factory {
	/**
	 * Makes an Actor.
	 *
	 * @param string|int $identifier
	 *
	 * @return Actor|null
	 */
	public function make( $identifier );

	/**
	 * Gets the actor type slug.
	 *
	 * @return string
	 */
	public function get_slug();

	/**
	 * Gets the label
	 *
	 * @return string
	 */
	public function get_label();

	/**
	 * Does this have a pre-determined list of Actors, or is the list infinite.
	 *
	 * @return bool
	 */
	public function is_determinate();

	/**
	 * Gets the list of Actors.
	 *
	 * @param string $query An optional search query.
	 *
	 * @return Actor[]
	 */
	public function get_actors( $query = '' );
}
