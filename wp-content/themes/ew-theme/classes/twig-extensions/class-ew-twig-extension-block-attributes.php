<?php

namespace EwStarter;

use Twig_Extension;
use Twig_SimpleFunction;

/**
 * Class Ew_Twig_Extension_Block_Attributes
 * @package EwStarter
 */
class Ew_Twig_Extension_Block_Attributes extends Twig_Extension {

	/**
	 * Get functions.
	 *
	 * @return array|Twig_SimpleFunction[]
	 */
	public function getFunctions() {
		return [
			new Twig_SimpleFunction( 'get_attr', [ $this, 'get_component_attr' ] ),
		];
	}

    /**
     * Get components attribute from blocks attributes array
     * @param $prefix string Component prefix
     * @param $attribute string Attribute name
     * @param $attributes object/array Attributes array
     * @return mixed
     */
	public function get_component_attr( $prefix, $attribute, $attributes ) {
	    $capitalized_attribute = ucfirst($attribute);
	    return $attributes["$prefix$capitalized_attribute"] ?? null;
	}

}