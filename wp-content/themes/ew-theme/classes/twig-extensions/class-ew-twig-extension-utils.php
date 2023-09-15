<?php

namespace EwStarter;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class Ew_Twig_Extension_Utils
 * @package EwStarter
 */
class Ew_Twig_Extension_Utils extends AbstractExtension {

	/**
	 * Get functions.
	 *
	 * @return array
	 */
	public function getFunctions(): array
	{
		return [
			new TwigFunction( 'is_admin_logged_in', [ $this, 'is_admin_logged_in' ] ),
			new TwigFunction( 'get_theme_url', [ $this, 'get_theme_url' ] ),
			new TwigFunction( 'function', [ $this, 'exec_function' ] ),
		];
	}

	/**
	 * Returns true if current user is admin; false otherwise
	 *
	 * @return bool
	 */
	public function is_admin_logged_in(): bool
	{
		return current_user_can( 'administrator' );
	}

	/**
	 * Returns the theme URL
	 *
	 * @return string
	 */
	public function get_theme_url(): string
	{
		return THEME_URL;
	}

	/**
	 * Execute function.
	 *
	 * @param string $function_name
	 *
	 * @return mixed
	 */
	public function exec_function( $function_name ): mixed
	{
		// Get arguments
		$args = func_get_args();

		// Shift array
		array_shift( $args );

		// Is string
		if ( is_string( $function_name ) ) {

			// Trim function name
			$function_name = trim( $function_name );
		}

		// Returns
		return call_user_func_array( $function_name, ( $args ) );
	}
}
