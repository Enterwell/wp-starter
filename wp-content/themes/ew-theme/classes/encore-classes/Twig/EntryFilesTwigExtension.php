<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EwStarter;

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
            new TwigFunction('encore_entry_script_tags', [$this, 'renderWebpackScriptTags'], ['is_safe' => ['html']]),
            new TwigFunction('encore_entry_link_tags', [$this, 'renderWebpackLinkTags'], ['is_safe' => ['html']])
        ];
    }

    public function renderWebpackScriptTags(string $entryName, string $packageName = null, string $entrypointName = '_default'): string
    {
        if($packageName !== null) {
            throw new \InvalidArgumentException('packageName not implemented');
        }
        return $this->getTagRenderer()
            ->renderWebpackScriptTags($entryName, $packageName, $entrypointName);
    }

    public function renderWebpackLinkTags(string $entryName, string $packageName = null, string $entrypointName = '_default'): string
    {
        if($packageName !== null) {
            throw new \InvalidArgumentException('packageName not implemented');
        }
        return $this->getTagRenderer()
            ->renderWebpackLinkTags($entryName, $packageName, $entrypointName);
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
