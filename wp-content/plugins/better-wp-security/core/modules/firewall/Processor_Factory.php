<?php

namespace iThemesSecurity\Modules\Firewall;

use iThemesSecurity\Modules\Firewall\Rules\Loader;
use iThemesSecurity\Strauss\Patchstack\Extensions\ExtensionInterface;
use iThemesSecurity\Strauss\Patchstack\Processor;

class Processor_Factory {

	/** @var ExtensionInterface */
	private $extension;

	/** @var Loader */
	private $rules;

	public function __construct( ExtensionInterface $extension, Loader $rules ) {
		$this->extension = $extension;
		$this->rules     = $rules;
	}

	/**
	 * Makes a new Firewall Processor.
	 *
	 * @param bool $loading_early True if this Processor will be used to process rules early,
	 *                            before other plugins have run.
	 *
	 * @return Processor
	 */
	public function make( bool $loading_early ): Processor {
		return new Processor(
			$this->extension,
			$this->rules->load_rules(),
			[],
			[
				'mustUsePluginCall' => $loading_early,
				'autoblockTime'     => \ITSEC_Modules::get_setting( 'global', 'lockout_period' ),
				'autoblockMinutes'  => \ITSEC_Modules::get_setting( 'firewall', 'check_period' ),
				'autoblockAttempts' => \ITSEC_Modules::get_setting( 'firewall', 'max_attempts_host' ),
			]
		);
	}
}
