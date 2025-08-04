<?php

namespace EwStarter;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class Ew_Twig_Extension_Block_Attributes
 * @package EwStarter
 */
class Ew_Twig_Extension_Block_Attributes extends AbstractExtension {

	/**
	 * Get functions.
	 *
	 * @return array
	 */
	public function getFunctions(): array
	{
		return [
			new TwigFunction( 'get_attr', [ $this, 'get_component_attr' ] ),
		];
	}

    /**
     * Get components attribute from blocks attributes array
     * @param $prefix string Component prefix
     * @param $attribute string Attribute name
     * @param $attributes object/array Attributes array
     * @return mixed
     */
	public function get_component_attr( $prefix, $attribute, $attributes ): mixed
	{
	    $capitalized_attribute = ucfirst($attribute);
	    return $attributes["$prefix$capitalized_attribute"] ?? null;
	}

}
