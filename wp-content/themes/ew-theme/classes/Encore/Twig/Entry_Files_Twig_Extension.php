<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EwStarter\Encore\Twig;

use EwStarter\Encore\Asset\Entrypoint_Lookup;
use EwStarter\Encore\Asset\Entrypoint_Lookup_Collection;
use EwStarter\Encore\Asset\Tag_Renderer;
use EwStarter\Encore\Asset\Tag_Renderer_Interface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Twig extension that renders encore tags.
 */
final class Entry_Files_Twig_Extension extends AbstractExtension
{
	/** @var array|mixed */
	private $container;

	/** @var ?Tag_Renderer_Interface */
	private ?Tag_Renderer_Interface $tag_renderer = null;

	public function __construct($container)
	{
		if (!\is_array($container))
			$container = ['_default' => $container];

		$this->container = $container;
	}

	public function getFunctions()
	{
		return [
			new TwigFunction('encore_entry_script_tags', [$this, 'render_webpack_script_tags'], ['is_safe' => ['html']]),
			new TwigFunction('encore_entry_link_tags', [$this, 'render_webpack_link_tags'], ['is_safe' => ['html']])
		];
	}

	/**
	 * @param string $entryName
	 * @param array $attributes
	 * @param string|null $packageName
	 * @param string $entrypointName
	 * @return string
	 */
	public function render_webpack_script_tags(string $entryName, array $attributes = [], string $packageName = null, string $entrypointName = '_default'): string
	{
		if ($packageName !== null) {
			throw new \InvalidArgumentException('packageName not implemented');
		}
		return $this->get_tag_renderer()
			->render_webpack_script_tags($entryName, $packageName, $entrypointName, $attributes);
	}

	/**
	 * @param string $entryName
	 * @param array $attributes
	 * @param string|null $packageName
	 * @param string $entrypointName
	 * @return string
	 */
	public function render_webpack_link_tags(string $entryName, array $attributes = [], string $packageName = null, string $entrypointName = '_default'): string
	{
		if ($packageName !== null) {
			throw new \InvalidArgumentException('packageName not implemented');
		}
		return $this->get_tag_renderer()
			->render_webpack_link_tags($entryName, $packageName, $entrypointName, $attributes);
	}

	/**
	 * @return Tag_Renderer_Interface
	 */
	private function get_tag_renderer(): Tag_Renderer_Interface
	{
		if ($this->tag_renderer === null) {
			$entrypointLookups = [];
			foreach ($this->container as $key => $container) {
				$entrypointLookups[$key] = new Entrypoint_Lookup($container);
			}
			$this->tag_renderer = new Tag_Renderer(
				new Entrypoint_Lookup_Collection($entrypointLookups)
			);
		}
		return $this->tag_renderer;
	}
}
