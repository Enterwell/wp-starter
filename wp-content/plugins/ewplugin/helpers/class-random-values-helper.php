<?php
/**
 * Created by PhpStorm.
 * User: mateikki
 * Date: 31.8.2020.
 * Time: 14:55
 */

namespace EwStarter;

/**
 * Class Random_Values_Helper
 * @package EwStarter
 */
class Random_Values_Helper {
	/**
	 * Gets random string of specified length.
	 *
	 * @since 1.0.0
	 *
	 * @param int $length
	 *
	 * @return string
	 */
	public static function get_random_string( int $length = 10 ): string {
		$characters  = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$char_length = strlen( $characters );
		$rand_string = '';
		for ( $i = 0; $i < $length; $i ++ ) {
			$rand_string .= $characters[ rand( 0, $char_length - 1 ) ];
		}

		return $rand_string;
	}
}
