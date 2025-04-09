<?php

add_action( 'admin_enqueue_scripts', function () {
	wp_enqueue_script( 'itsec-pages-go-pro' );
	wp_enqueue_style( 'itsec-pages-go-pro' );
} );

add_action( 'itsec-page-show', function () {
	printf(
		'<div id="itsec-go-pro-root"></div>'
	);
} );
