# Custom Encore classes used for adding scripts to pages

_Based on https://packagist.org/packages/rodrigoq/simple-webpack-encore-bundle_

Simple classes that provide Twig and PHP functions used for adding scripts and links to pages. Made for Webpack Encore -> WP starter integration.

#### Examples
```twig
    {{ encore_entry_script_tags('app') }}
    {{ encore_entry_link_tags('app') }}
```
First argument represents Webpack Encore entry name. Can be found in `entrypoints.json` in themes `dist` folder.

Disclaimer:
- requires https://github.com/symfony/service-contracts for integration
