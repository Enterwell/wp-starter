<?php

if ( defined( 'ITSEC_DISABLE_TWO_FACTOR' ) && ITSEC_DISABLE_TWO_FACTOR ) {
	ITSEC_Lib_Admin_Notices::register(
		new ITSEC_Admin_Notice_Managers_Only(
			new ITSEC_Admin_Notice_Callback( 'two-factor-disabled-constant', 'itsec_two_factor_get_disabled_notice', null, ITSEC_Admin_Notice::S_ERROR )
		)
	);
}

function itsec_two_factor_get_disabled_notice() {
	return sprintf( esc_html__( 'The %1$sITSEC_DISABLE_TWO_FACTOR%2$s define is present. As long as the define is present, two-factor authentication is disabled for all users which makes your site more vulnerable. Please make any necessary changes and remove the define as soon as possible.', 'better-wp-security' ), '<code>', '</code>' );
}
