<?php
/**
 * Theme functions, hooks registration, and other
 * theme related contents.
 */

use Ew\Ew_Theme;
use Ew\Ew_Twig;

// Include constants
require_once( 'constants.php' );

// Require composer
require_once( 'vendor/autoload.php' );

// Require Ew_Twig class
require_once( 'classes/class-ew-twig.php' );

// Require Ew_Theme class
require_once( 'classes/class-ew-theme.php' );

// Initialize twig
$ew_twig = new Ew_Twig();

// Load class
Ew_Theme::load();

