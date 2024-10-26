<?php

namespace EwStarter\Public\Interfaces;

/**
 * Admin plugin interface.
 */
interface Plugin_Public_Interface {
	/**
	 * Function used to enqueue admin scripts.
	 */
	function enqueue_scripts(): void;

	/**
	 * Used to enqueue admin styles.
	 */
	function enqueue_styles(): void;
}
