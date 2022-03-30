<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EwStarter\Encore\Asset;

use EwStarter\Encore\Exception\Undefined_Build_Exception;

/**
 * Aggregate the different entry points configured in the container.
 *
 * Retrieve the EntrypointLookup instance from the given key.
 *
 * @final
 */
class Entrypoint_Lookup_Collection implements Entrypoint_Lookup_Collection_Interface
{
	/** @var array */
	private array $build_entrypoints;

	/** @var string */
	private string $default_build_name;

	/**
	 * @param array $build_entrypoints
	 * @param string $default_build_name
	 */
	public function __construct(array $build_entrypoints, string $default_build_name = '_default')
	{
		$this->build_entrypoints = $build_entrypoints;
		$this->default_build_name = $default_build_name;
	}

	/**
	 * @param string|null $buildName
	 * @return Entrypoint_Lookup_Interface
	 */
	public function get_entrypoint_lookup(?string $buildName = null): Entrypoint_Lookup_Interface
	{
		if (null === $buildName) {
			if (null === $this->default_build_name) {
				throw new Undefined_Build_Exception('There is no default build configured: please pass an argument to getEntrypointLookup().');
			}

			$buildName = $this->default_build_name;
		}

		if (!isset($this->build_entrypoints[$buildName])) {
			throw new Undefined_Build_Exception(sprintf('The build "%s" is not configured', $buildName));
		}

		return $this->build_entrypoints[$buildName];
	}
}
