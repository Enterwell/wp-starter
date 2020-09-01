<?php

require_once dirname( __FILE__ ) . '/class-itsec-site-scanner.php';
$scanner = new ITSEC_Site_Scanner();
$scanner->run();
