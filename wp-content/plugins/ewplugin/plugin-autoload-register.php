<?php

spl_autoload_register( function ( $src_class ) {
	$namespace_prefix = PLUGIN_NAMESPACE . '\\';
	if ( ! str_starts_with( $src_class, $namespace_prefix ) ) {
		return;
	}

	// Remove plugin namespace from the class name
	$class = substr( $src_class, strlen( $namespace_prefix ) );

	if ( str_contains( $class, '\\' ) ) {
		$name_parts   = explode( '\\', $class );
		$class_name   = array_pop( $name_parts );
		$class_folder = array_map( 'strtolower', $name_parts );
	} else {
		$class_name   = $class;
		$class_folder = [];
	}

	$file_name_parts = array_map( 'strtolower', explode( '_', $class_name ) );
	if ( $file_name_parts[ count( $file_name_parts ) - 1 ] === 'interface' ) {
		// Remove interface from the end of the file name
		array_pop( $file_name_parts );
		$file_path = PLUGIN_DIR . implode( '/', $class_folder ) . '/interface-' . implode( '-', $file_name_parts ) . '.php';
	} else {
		$file_path = PLUGIN_DIR . implode( '/', $class_folder ) . '/class-' . implode( '-', $file_name_parts ) . '.php';
	}

	if ( file_exists( $file_path ) ) {
		require_once $file_path;
	} else {
		throw new Exception( esc_html( "Class $src_class could not be found." ) );
	}
} );
