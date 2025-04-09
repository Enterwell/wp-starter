<?php

use iThemesSecurity\Lib\Tools\Tools_Registry;
use iThemesSecurity\Lib\Tools\Tools_Runner;

final class ITSEC_Database_Prefix_Utility {
	public static function change_database_prefix() {
		_deprecated_function( __METHOD__, '7.0.0', Tools_Runner::class );

		$response = [
			'errors'     => [],
			'new_prefix' => false,
		];

		$runner   = ITSEC_Modules::get_container()->get( Tools_Runner::class );
		$registry = ITSEC_Modules::get_container()->get( Tools_Registry::class );

		$result = $runner->run_tool( $registry->get_tool( 'change-database-prefix' ) );

		if ( $result->is_success() ) {
			$response['new_prefix'] = $result->get_data();

			foreach ( $result->get_warning_messages() as $message ) {
				$response['errors'][] = new WP_Error( 'itsec-db-prefix-warning', $message );
			}
		} else {
			$response['errors'][] = $result->get_error();
		}

		return $response;
	}
}
