<?php

namespace EwStarter;

use EwStarter\Main\Plugin_Activator;
use EwStarter\Main\Plugin_Deactivator;
use EwStarter\Main\Plugin;

require_once plugin_dir_path( __FILE__ ) . 'constants.php';
require_once plugin_dir_path( __FILE__ ) . 'plugin-autoload-register.php';

// Register activation/deactivation hooks
register_activation_hook( __FILE__, [ Plugin_Activator::class, 'activate' ] );
register_deactivation_hook( __FILE__, [ Plugin_Deactivator::class, 'deactivate' ] );

$plugin = new Plugin();
$plugin->run();
