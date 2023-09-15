<?php

abstract class ITSEC_REST_Dashboard_Controller extends WP_REST_Controller {

	/**
	 * Build the allow data for Target Hints.
	 *
	 * @param string $route
	 * @param array  $url_params
	 *
	 * @return string[]
	 */
	protected function build_allow_target_hints( $route, $url_params ) {

		$request = new WP_REST_Request( '', $route );
		$request->set_url_params( $url_params );

		$allow = array();

		$request->set_method( 'GET' );
		if ( true === $this->get_item_permissions_check( $request ) ) {
			$allow[] = 'GET';
		}

		$request->set_method( 'PUT' );
		if ( true === $this->update_item_permissions_check( $request ) ) {
			$allow[] = 'PUT';
		}

		$request->set_method( 'DELETE' );
		if ( true === $this->delete_item_permissions_check( $request ) ) {
			$allow[] = 'DELETE';
		}

		return $allow;
	}
}
