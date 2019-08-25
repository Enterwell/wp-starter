<?php
/* (c) Anton Medvedev <anton@medv.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Set configuration
namespace Deployer;


use Ew\Deploy;
use Symfony\Component\Console\Input\InputArgument;

define( 'DEPLOYER_CONFIG_DIR_PATH', realpath( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'deployer' ) );

// Load deployer config class
require_once DEPLOYER_CONFIG_DIR_PATH . '/class-deployer-config.php';
require_once DEPLOYER_CONFIG_DIR_PATH . '/class-deploy.php';


// Load common recipe
require_once 'recipe/common.php';

argument( Deploy::SKIP_JS_BUILD_ARG, InputArgument::OPTIONAL, 'Argument that instructs deploy to skip js build.' );

/** @noinspection PhpUnhandledExceptionInspection */
$deploy = new Deploy();
$deploy->define_tasks();
