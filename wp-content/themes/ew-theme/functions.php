<?php
/**
 * Theme functions, hooks registration, and other
 * theme related contents.
 */

use EwStarter\EW_Encore_Classes_Loader;
use EwStarter\Ew_Theme;
use EwStarter\Ew_Blocks;
use EwStarter\Ew_Twig;
use EwStarter\Theme_Hooks;

// Include constants
require_once 'constants.php';

// Require composer
require_once 'vendor/autoload.php';

// Require Encore classes
require_once 'classes/class-ew-encore-classes-loader.php';

// Require Ew_Twig class
require_once 'classes/class-ew-twig.php';

// Require Ew_Theme class
require_once 'classes/class-ew-theme.php';

// Require blocks
require_once 'classes/class-ew-blocks.php';

// Require Theme_Hooks class
require_once 'classes/class-theme-hooks.php';

// Load Encore classes
EW_Encore_Classes_Loader::load();

// Initialize twig
$ew_twig = new Ew_Twig();

// Load class
Ew_Theme::load();

// Load blocks
Ew_Blocks::load();

// Load theme hooks
Theme_Hooks::load();
