<?php

namespace EwStarter\Main\Interfaces;

interface Plugin_Activator_Interface {
	/**
	 * Activate the plugin.
	 */
	public function activate(): void;
}
