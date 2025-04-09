<?php

namespace iThemesSecurity\Actor;

interface Actor {

	/**
	 * Gets the type of this actor.
	 *
	 * @return string
	 */
	public function get_type();

	/**
	 * Gets the identifier for this particular actor instance.
	 *
	 * This must be unique to the actor type.
	 *
	 * @return string
	 */
	public function get_identifier();

	/**
	 * Gets a description of this actor.
	 *
	 * @return string
	 */
	public function __toString();
}
