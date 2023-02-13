<?php

$settings = ITSEC_Modules::get_settings( 'backup' );

if ( $settings['enabled'] && $settings['interval'] > 0 ) {
	ITSEC_Core::get_scheduler()->register_custom_schedule( 'backup', DAY_IN_SECONDS * $settings['interval'] );
	ITSEC_Core::get_scheduler()->schedule( 'backup', 'backup' );
}
