<?php

namespace EwStarter;

/**
 * Class EW_Encore_Classes_Loader
 * @package EwStarter
 */
class EW_Encore_Classes_Loader {

	/**
	 * Loads encore classes
	 */
	public static function load() {
		// Load exceptions
		require_once 'encore-classes/Exception/EntrypointNotFoundException.php';
		require_once 'encore-classes/Exception/UndefinedBuildException.php';

		// Load assets
		require_once 'encore-classes/Asset/EntrypointLookupInterface.php';
		require_once 'encore-classes/Asset/EntrypointLookupCollectionInterface.php';
		require_once 'encore-classes/Asset/IntegrityDataProviderInterface.php';
		require_once 'encore-classes/Asset/EntrypointLookup.php';
		require_once 'encore-classes/Asset/EntrypointLookupCollection.php';
		require_once 'encore-classes/Asset/TagRenderer.php';

		// Load twig extension classes
		require_once 'encore-classes/Twig/EntryFilesTwigExtension.php';
	}
}
