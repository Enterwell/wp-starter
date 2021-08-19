<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Simple\WebpackEncoreBundle\Twig;

use Simple\WebpackEncoreBundle\Asset\EntrypointLookup;
use Simple\WebpackEncoreBundle\Asset\EntrypointLookupCollection;
use Simple\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Simple\WebpackEncoreBundle\Asset\TagRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class EntryFilesTwigExtension extends AbstractExtension
{
    private $container;

    private $tagRenderer = null;

    public function __construct($container)
    {
        if(!\is_array($container))
            $container = ['_default' => $container];

        $this->container = $container;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('encore_entry_js_files', [$this, 'getWebpackJsFiles']),
            new TwigFunction('encore_entry_css_files', [$this, 'getWebpackCssFiles']),
            new TwigFunction('encore_entry_script_tags', [$this, 'renderWebpackScriptTags'], ['is_safe' => ['html']]),
            new TwigFunction('encore_entry_link_tags', [$this, 'renderWebpackLinkTags'], ['is_safe' => ['html']]),
            // New custom functions
            new TwigFunction('encore_entry_script_tags_with_baseurl', [$this, 'renderWebpackScriptTagsWithBaseUrl'], ['is_safe' => ['html']]),
            new TwigFunction('encore_entry_link_tags_with_baseurl', [$this, 'renderWebpackLinkTagsWithBaseUrl'], ['is_safe' => ['html']]),
        ];
    }

    public function getWebpackJsFiles(string $entryName, string $entrypointName = '_default'): array
    {
        return $this->getEntrypointLookup($entrypointName)
            ->getJavaScriptFiles($entryName);
    }

    public function getWebpackCssFiles(string $entryName, string $entrypointName = '_default'): array
    {
        return $this->getEntrypointLookup($entrypointName)
            ->getCssFiles($entryName);
    }

    public function renderWebpackScriptTagsWithBaseUrl(string $entryName, string $baseUrl = '', string $entrypointName = '_default'): string
    {
        return $this->getTagRenderer()
            ->renderWebpackScriptTagsWithBaseUrl($entryName, $baseUrl, $entrypointName);
    }

    public function renderWebpackScriptTags(string $entryName, string $packageName = null, string $entrypointName = '_default'): string
    {
        if($packageName !== null) {
            throw new \InvalidArgumentException('packageName not implemented');
        }
        return $this->getTagRenderer()
            ->renderWebpackScriptTags($entryName, $packageName, $entrypointName);
    }

    public function renderWebpackLinkTagsWithBaseUrl(string $entryName, string $baseUrl = '', string $entrypointName = '_default'): string
    {
        return $this->getTagRenderer()
            ->renderWebpackLinkTagsWithBaseUrl($entryName, $baseUrl, $entrypointName);
    }

    public function renderWebpackLinkTags(string $entryName, string $packageName = null, string $entrypointName = '_default'): string
    {
        if($packageName !== null) {
            throw new \InvalidArgumentException('packageName not implemented');
        }
        return $this->getTagRenderer()
            ->renderWebpackLinkTags($entryName, $packageName, $entrypointName);
    }

    private function getEntrypointLookup(string $entrypointName): EntrypointLookupInterface
    {
        return new EntrypointLookup($this->container[$entrypointName]);
    }

    private function getTagRenderer(): TagRenderer
    {
        if($this->tagRenderer === null) {
            $entrypointLookups = [];
            foreach($this->container as $key => $container) {
                $entrypointLookups[$key] = new EntrypointLookup($container);
            }
            $this->tagRenderer = new TagRenderer(
                new EntrypointLookupCollection($entrypointLookups)
            );
        }
        return $this->tagRenderer;
    }
}
