<?php
require_once( dirname( __FILE__ ) . '/class-itsec-core-active.php' );
require_once( dirname( __FILE__ ) . '/class-itsec-core-admin.php' );
require_once( dirname( __FILE__ ) . '/class-itsec-admin-notices.php' );

$active = new ITSEC_Core_Active();
$active->run();

$admin = new ITSEC_Core_Admin();
$admin->run();

$notices = new ITSEC_Admin_Notices();
$notices->run();
