<?php
/**
 * Created by PhpStorm.
 * User: Matej
 * Date: 30.8.2018.
 * Time: 15:22
 */

namespace Ew\WpHelpers\Services;

/**
 * Interface ICRUD_Service
 * @package Ew
 */
interface ICRUD_Service {
	/**
	 * @param $r
	 *
	 * @return mixed
	 */
	function validate_create_request( $r );

	/**
	 * @param $r
	 *
	 * @return mixed
	 */
	function create_from_request( $r );

	/**
	 * @param $entity_id
	 * @param $r
	 *
	 * @return mixed
	 */
	function update_from_request( $entity_id, $r );
}