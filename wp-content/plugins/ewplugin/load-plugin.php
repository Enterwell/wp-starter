<?php
namespace EwStarter;

use EwStarter\Main\DI_Container;
use EwStarter\Main\Plugin;
use EwStarter\Main\Interfaces\Plugin_Activator_Interface;

require_once plugin_dir_path( __FILE__ ) . 'constants.php';
require_once plugin_dir_path( __FILE__ ) . 'plugin-autoload-register.php';

$container = DI_Container::get_instance();

register_activation_hook( PLUGIN_FILE_DIR, function () use ( $container ) {
	$activator = $container->get( Plugin_Activator_Interface::class );
	$activator->activate();
} );

register_deactivation_hook( PLUGIN_FILE_DIR, function () use ( $container ) {
	// Does nothing for now since we don't do anything on deactivation
} );

$plugin = new Plugin( $container );
$plugin->run();
