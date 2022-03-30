<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EwStarter\Encore\Asset;

use EwStarter\Encore\Exception\Entrypoint_Not_Found_Exception;
use Symfony\Contracts\Service\ResetInterface;

interface Entrypoint_Lookup_Interface extends ResetInterface
{
	/**
	 * @param string $entry_name
	 * @return array
	 * @throws Entrypoint_Not_Found_Exception if an entry name is passed that does not exist in entrypoints.json
	 */
    public function get_javascript_files(string $entry_name): array;

	/**
	 * @param string $entry_name
	 * @return array
	 * @throws Entrypoint_Not_Found_Exception if an entry name is passed that does not exist in entrypoints.json
	 */
    public function get_css_files(string $entry_name): array;

    /**
     * Resets the state of this service.
     */
    public function reset();
}
