<?php
/**
 * Theme functions, hooks registration, and other
 * theme related contents.
 */

use EwStarter\Configuration\Ew_Theme;

// Include constants
require_once 'constants.php';
require_once 'helper-functions.php';

// Require composer
require_once 'vendor/autoload.php';

// Load theme
$theme = new Ew_Theme();
/** @noinspection PhpUnhandledExceptionInspection */
$theme->load();

