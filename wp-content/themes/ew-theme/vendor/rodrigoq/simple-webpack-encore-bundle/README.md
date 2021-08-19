# Simple WebpackEncoreBundle: Php and Twig integration with Webpack Encore!

A very basic and simple implementation of [WebpackEncoreBundle](https://github.com/symfony/webpack-encore-bundle)
for projects that do not use Symfony, with easier setup and less dependencies.
Recommended for old projects or projects that do not plan to add or setup Symfony.
The frontend setup is the same as with [Webpack Encore](https://symfony.com/doc/current/frontend.html)

Install with:

```
composer require rodrigoq/simple-webpack-encore-bundle
```

## Configuration

In your twig setup file:

```php
    use Simple\WebpackEncoreBundle\Twig\EntryFilesTwigExtension;
    ...
    $entrypointJsonFile = "path/to/entrypoints.json";
    $twigEnvironment->addExtension(new EntryFilesTwigExtension($entrypointJsonFile));
```

or for multiple builds:

```php
    use Simple\WebpackEncoreBundle\Twig\EntryFilesTwigExtension;
    ...
    $builds = [
        'firstConfig' => "path/to/firstConfig/entrypoints.json",
        'secondConfig' => "path/to/secondConfig/entrypoints.json",
    ];
    $twigEnvironment->addExtension(new EntryFilesTwigExtension($builds));
```

No need for yaml configuration file.

## Usage

Check readme on the [original repo](https://github.com/symfony/webpack-encore-bundle).

## Changes

Added two new twig functions for full URL or directory prefixing, you can have
a full path or different paths configured in php not needing for code on your
javascript configuration file.

```twig
    {{ encore_entry_script_tags_with_baseurl('app', 'https://yoursite.com') }}
    {{ encore_entry_link_tags_with_baseurl('app', 'https://yoursite.com') }}

    {# or any directory prefix #}

    {{ encore_entry_script_tags_with_baseurl('app', '/a/different/path') }}
    {{ encore_entry_link_tags_with_baseurl('app', '/a/different/path') }}
```

