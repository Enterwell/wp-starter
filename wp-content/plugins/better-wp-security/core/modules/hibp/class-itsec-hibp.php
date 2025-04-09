<?php

use iThemesSecurity\Lib\Password_Requirement;

/**
 * Class ITSEC_HIBP
 */
class ITSEC_HIBP implements \iThemesSecurity\Contracts\Runnable {

	/** @var Password_Requirement */
	private $requirement;

	/**
	 * ITSEC_HIBP constructor.
	 *
	 * @param Password_Requirement $requirement
	 */
	public function __construct( Password_Requirement $requirement ) { $this->requirement = $requirement; }

	/**
	 * Initialize the module.
	 */
	public function run() {
		add_action( 'itsec_register_password_requirements', array( $this, 'register_requirement' ) );
	}

	public function register_requirement() {
		ITSEC_Lib_Password_Requirements::register( $this->requirement );
	}
}
