<?php

if ( 'cli' !== php_sapi_name() ) {
	die( 1 );
}

if ( ! isset( $argv[1] ) ) {
	die( 1 );
}

$json = $argv[1];

if ( ! $decoded = json_decode( $json, true ) ) {
	die( 1 );
}

ksort( $decoded );

fwrite( STDOUT, var_export( $decoded, true ) );
die( 0 );
