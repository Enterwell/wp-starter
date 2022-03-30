<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EwStarter\Encore\Asset;

interface Entrypoint_Lookup_Collection_Interface
{
	/**
	 * Retrieve the EntrypointLookupInterface for the given build.
	 *
	 * @param string|null $buildName
	 * @return Entrypoint_Lookup_Interface
	 */
    public function get_entrypoint_lookup(string $buildName = null): Entrypoint_Lookup_Interface;
}
