<?php

register_rest_route( 'ithemes-security/rpc', 'file-writing/get-config-rules', [
	'callback'            => function () {
		return [
			'server' => ITSEC_Lib_Config_File::get_server_config(),
			'wp'     => ITSEC_Lib_Config_File::get_wp_config(),
		];
	},
	'permission_callback' => 'ITSEC_Core::current_user_can_manage',
] );
