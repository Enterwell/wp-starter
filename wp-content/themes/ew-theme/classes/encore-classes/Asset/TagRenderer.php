<?php

/*
 * This file is part of the Symfony WebpackEncoreBundle package.
 * (c) Fabien Potencier <fabien@symfony.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EwStarter;

use Symfony\Contracts\Service\ResetInterface;

/**
 * @final
 */
class TagRenderer implements ResetInterface
{
    private $entrypointLookupCollection;

    private $defaultAttributes;

    private $renderedFiles = [];

    public function __construct($entrypointLookupCollection, array $defaultAttributes = []) {
        if ($entrypointLookupCollection instanceof EntrypointLookupCollection) {
            $this->entrypointLookupCollection = $entrypointLookupCollection;
        } else {
            throw new \TypeError('The "$entrypointLookupCollection" argument must be an instance of EntrypointLookupCollection.');
        }
        $this->defaultAttributes = $defaultAttributes;

        $this->reset();
    }

    public function renderWebpackScriptTags(string $entryName, string $packageName = null, string $entrypointName = '_default', string $baseUrl = ''): string
    {
        if($packageName !== null) {
            throw new \InvalidArgumentException('packageName not implemented');
        }
        $scriptTags = [];
        $entryPointLookup = $this->getEntrypointLookup($entrypointName);
        $integrityHashes = ($entryPointLookup instanceof IntegrityDataProviderInterface) ? $entryPointLookup->getIntegrityData() : [];

        foreach ($entryPointLookup->getJavaScriptFiles($entryName) as $filename) {
            $attributes = $this->defaultAttributes;
            $attributes['src'] = $this->getAssetPath($filename, $packageName);

            if (isset($integrityHashes[$filename])) {
                $attributes['integrity'] = $integrityHashes[$filename];
            }

            $scriptTags[] = sprintf(
                '<script %s></script>',
                $this->convertArrayToAttributes($attributes, $baseUrl)
            );

            $this->renderedFiles['scripts'][] = $attributes['src'];
        }

        return implode('', $scriptTags);
    }

    public function renderWebpackLinkTags(string $entryName, string $packageName = null, string $entrypointName = '_default', string $baseUrl = ''): string
    {
        if($packageName !== null) {
            throw new \InvalidArgumentException('packageName not implemented');
        }
        $scriptTags = [];
        $entryPointLookup = $this->getEntrypointLookup($entrypointName);
        $integrityHashes = ($entryPointLookup instanceof IntegrityDataProviderInterface) ? $entryPointLookup->getIntegrityData() : [];

        foreach ($entryPointLookup->getCssFiles($entryName) as $filename) {
            $attributes = $this->defaultAttributes;
            $attributes['rel'] = 'stylesheet';
            $attributes['href'] = $this->getAssetPath($filename, $packageName);

            if (isset($integrityHashes[$filename])) {
                $attributes['integrity'] = $integrityHashes[$filename];
            }

            $scriptTags[] = sprintf(
                '<link %s>',
                $this->convertArrayToAttributes($attributes, $baseUrl)
            );

            $this->renderedFiles['styles'][] = $attributes['href'];
        }

        return implode('', $scriptTags);
    }

    public function getDefaultAttributes(): array
    {
        return $this->defaultAttributes;
    }

    public function reset()
    {
        $this->renderedFiles = [
            'scripts' => [],
            'styles' => [],
        ];
    }

    private function getAssetPath(string $assetPath, string $packageName = null): string
    {
        if($packageName !== null) {
            throw new \InvalidArgumentException('packageName not implemented');
        }

        //TODO: check if this always work.
        return $assetPath;
    }

    private function getEntrypointLookup(string $buildName): EntrypointLookupInterface
    {
        return $this->entrypointLookupCollection->getEntrypointLookup($buildName);
    }

    private function convertArrayToAttributes(array $attributesMap, string $baseUrl = ''): string
    {
        return implode(' ', array_map(
            function ($key, $value) use ($baseUrl) {
                return sprintf('%s="%s"', $key, $this->getValue($key, $baseUrl, $value));
            },
            array_keys($attributesMap),
            $attributesMap
        ));
    }

    private function getValue($key, $baseUrl, $value) : string
    {
        if($key == 'src' || $key == 'href') {
            return htmlentities($baseUrl . $value);
        }
        return htmlentities($value);
    }
}
