<?php

namespace iThemesSecurity\TwoFactor;

use iThemesSecurity\User_Groups\Matcher;
use iThemesSecurity\User_Groups;
use ITSEC_Modules;

class Application_Passwords_Core implements \iThemesSecurity\Contracts\Runnable {
	public function run() {
		add_filter( 'wp_is_application_passwords_available_for_user', [ $this, 'restrict_availability' ], 10, 2 );
		add_filter( 'manage_application-passwords-user_columns', [ $this, 'register_table_columns' ] );
		add_action( 'manage_application-passwords-user_custom_column', [ $this, 'render_table_columns' ], 10, 2 );
		add_action( 'manage_application-passwords-user_custom_column_js_template', [ $this, 'render_table_columns_js_template' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_create_application_password_form', [ $this, 'render_form_fields' ] );
		add_action( 'wp_authorize_application_password_form', [ $this, 'render_auth_app_fields' ] );
		add_action( 'rest_api_init', [ $this, 'register_rest_fields' ] );
		add_action( 'wp_authenticate_application_password_errors', [ $this, 'check_app_password' ], 10, 3 );
	}

	/**
	 * Restricts App Passwords availability to the selected user groups.
	 *
	 * @param bool     $is_available If App Passwords is available.
	 * @param \WP_User $user         The user to check.
	 *
	 * @return bool
	 */
	public function restrict_availability( $is_available, $user ) {
		if ( ! $is_available ) {
			return $is_available;
		}

		$matcher = ITSEC_Modules::get_container()->get( Matcher::class );

		remove_filter( 'wp_is_application_passwords_available_for_user', [ $this, 'restrict_availability' ] );
		$is_available = $matcher->matches(
			User_Groups\Match_Target::for_user( $user ),
			ITSEC_Modules::get_setting( 'two-factor', 'application_passwords_group' )
		);
		add_filter( 'wp_is_application_passwords_available_for_user', [ $this, 'restrict_availability' ], 10, 2 );

		return $is_available;
	}

	/**
	 * Registers our custom columns in the passwords list table.
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function register_table_columns( $columns ) {
		$columns = \ITSEC_Lib::array_insert_after( 'name', $columns, 'rest_api_permissions', esc_html__( 'REST API Permissions', 'better-wp-security' ) );
		$columns = \ITSEC_Lib::array_insert_after( 'name', $columns, 'enabled_for', esc_html__( 'API Types', 'better-wp-security' ) );

		return $columns;
	}

	/**
	 * Renders our custom columns in the passwords list table.
	 *
	 * @param string $name The column name.
	 * @param array  $item The password item.
	 */
	public function render_table_columns( $name, $item ) {
		global $user_id;

		switch ( $name ) {
			case 'enabled_for':
				$enabled_for = $this->get_enabled_for( $user_id, $item );

				if ( array( 'xml-rpc' ) === $enabled_for ) {
					esc_html_e( 'XML-RPC', 'better-wp-security' );
				} elseif ( array( 'rest-api' ) === $enabled_for ) {
					esc_html_e( 'REST API', 'better-wp-security' );
				} else {
					esc_html_e( 'REST API and XML-RPC', 'better-wp-security' );
				}
				break;
			case 'rest_api_permissions':
				$enabled_for = $this->get_enabled_for( $user_id, $item );
				$permissions = $this->get_rest_api_permissions( $user_id, $item );

				if ( ! in_array( 'rest-api', $enabled_for, true ) ) {
					echo '&mdash;';
				} elseif ( 'read' === $permissions ) {
					esc_html_e( 'Read-Only', 'better-wp-security' );
				} else {
					esc_html_e( 'Read and Write', 'better-wp-security' );
				}
				break;
		}
	}

	/**
	 * Renders the JS underscores template for our custom columns.
	 *
	 * @param string $name The column name.
	 */
	public function render_table_columns_js_template( $name ) {
		switch ( $name ) {
			case 'enabled_for':
				echo "<# if ( data.enabled_for.includes( 'rest-api' ) && ! data.enabled_for.includes( 'xml-rpc' ) ) { #>";
				esc_html_e( 'REST API', 'better-wp-security' );
				echo "<# } else if ( data.enabled_for.includes( 'xml-rpc' ) && ! data.enabled_for.includes( 'rest-api' ) ) { #>";
				esc_html_e( 'XML-RPC', 'better-wp-security' );
				echo "<# } else { #>";
				esc_html_e( 'REST API and XML-RPC', 'better-wp-security' );
				echo '<# } #>';
				break;
			case 'rest_api_permissions':
				echo "<# if ( data.enabled_for.includes( 'rest-api' ) ) { #>";
				echo "<# print( data.rest_api_permissions === 'read' ? '" . __( 'Read-Only', 'better-wp-security' ) . "' : '" . __( 'Read and Write', 'better-wp-security' ) . "' ) #>";
				echo '<# } else { #>';
				echo '&mdash;';
				echo '<# } #>';
				break;
		}
	}

	/**
	 * Enqueues our custom JS on the user profile page.
	 */
	public function enqueue_scripts() {
		$id = get_current_screen()->id;

		if ( in_array( $id, [ 'profile', 'user-edit' ] ) ) {
			wp_enqueue_script( 'itsec-application-passwords-core', plugin_dir_url( __FILE__ ) . 'js/application-passwords-core.js', [ 'wp-dom-ready' ], 1 );
		}

		if ( 'authorize-application' === $id ) {
			wp_enqueue_script( 'itsec-application-passwords-auth-app', plugin_dir_url( __FILE__ ) . 'js/auth-app.js', [ 'wp-dom-ready' ], 1 );
		}
	}

	/**
	 * Renders our custom form fields.
	 */
	public function render_form_fields() {
		echo '<div class="form-field">';
		echo '<label for="itsec-app-password-enabled_for">' . esc_html__( 'API Types', 'better-wp-security' ) . '</label>';
		echo '<select id="itsec-app-password-enabled_for">';
		echo '<option value="all">' . esc_html__( 'All API Types', 'better-wp-security' ) . '</option>';
		echo '<option value="rest-api">' . esc_html__( 'REST API', 'better-wp-security' ) . '</option>';
		echo '<option value="xml-rpc">' . esc_html__( 'XML-RPC', 'better-wp-security' ) . '</option>';
		echo '</select>';
		echo '</div>';

		echo '<div class="form-field">';
		echo '<input type="checkbox" id="itsec-app-password-rest_api_permissions" value="1">';
		echo '<label for="itsec-app-password-rest_api_permissions">' . esc_html__( 'Read Only', 'better-wp-security' ) . '</label>';
		echo '<p class="description">';
		esc_html_e( 'This application password can access, but not modify data.', 'better-wp-security' );
		echo '</p>';
		echo '</div>';
	}

	/**
	 * Renders fields on the Authorize Application page.
	 */
	public function render_auth_app_fields() {
		echo '<div class="form-field">';
		echo '<label for="itsec-app-password-rest_api_permissions">' . esc_html__( 'Read Only', 'better-wp-security' ) . '</label>';
		echo '<input type="checkbox" id="itsec-app-password-rest_api_permissions" value="1" name="rest_api_permissions">';
		echo '<p class="description">';
		esc_html_e( 'This application password can access, but not modify data.', 'better-wp-security' );
		echo '</p>';
		echo '</div>';
	}

	/**
	 * Registers our fields in the REST API endpoint.
	 */
	public function register_rest_fields() {
		register_rest_field( 'application-password', 'rest_api_permissions', [
			'get_callback'    => function ( $item, $field, $request ) {
				$user_id = $request['user_id'] === 'me' ? get_current_user_id() : $request['user_id'];

				return $this->get_rest_api_permissions( $user_id, $item );
			},
			'update_callback' => function ( $value, $item, $field, $request ) {
				$user_id = $request['user_id'] === 'me' ? get_current_user_id() : $request['user_id'];
				update_user_meta( $user_id, "_{$item['uuid']}_rest_api_permissions", $value );
			},
			'schema'          => [
				'type'    => 'string',
				'enum'    => array( 'read', 'write' ),
				'default' => 'write',
			],
		] );

		register_rest_field( 'application-password', 'enabled_for', [
			'get_callback'    => function ( $item, $field, $request ) {
				$user_id = $request['user_id'] === 'me' ? get_current_user_id() : $request['user_id'];

				return $this->get_enabled_for( $user_id, $item );
			},
			'update_callback' => function ( $value, $item, $field, $request ) {
				$user_id = $request['user_id'] === 'me' ? get_current_user_id() : $request['user_id'];
				update_user_meta( $user_id, "_{$item['uuid']}_enabled_for", $value );
			},
			'schema'          => [
				'type'    => 'array',
				'items'   => array(
					'type' => 'string',
					'enum' => array( 'rest-api', 'xml-rpc' ),
				),
				'default' => array( 'rest-api', 'xml-rpc' ),
			],
		] );
	}

	/**
	 * Check the app password before it is used for permissions and API types.
	 *
	 * @param \WP_Error $error The error object to add to.
	 * @param \WP_User  $user  The user authenticating.
	 * @param array     $item  The password details.
	 */
	public function check_app_password( $error, $user, $item ) {
		$enabled_for = $this->get_enabled_for( $user->ID, $item );
		$permissions = $this->get_rest_api_permissions( $user->ID, $item );

		if ( \ITSEC_Core::is_rest_api_request() && ! in_array( 'rest-api', $enabled_for, true ) ) {
			$error->add( 'invalid_api_type', __( 'This application password is not allowed to be used with the REST API.', 'better-wp-security' ) );
		} elseif ( \ITSEC_Core::is_xmlrpc_request() && ! in_array( 'xml-rpc', $enabled_for, true ) ) {
			$error->add( 'invalid_api_type', __( 'This application password is not allowed to be used with XML-RPC.', 'better-wp-security' ) );
		}

		if (
			\ITSEC_Core::is_rest_api_request() &&
			'read' === $permissions &&
			! in_array( \ITSEC_Lib_REST::get_http_method(), [ 'GET', 'HEAD', 'OPTIONS' ], true )
		) {
			$error->add( 'unsupported_http_method', __( 'This application password is read only.', 'better-wp-security' ), array( 'status' => 403 ) );
		}
	}

	/**
	 * Gets the configured REST API permissions for the password.
	 *
	 * @param int   $user_id The user id.
	 * @param array $item    The password details.
	 *
	 * @return string
	 */
	protected function get_rest_api_permissions( $user_id, $item ) {
		$key         = "_{$item['uuid']}_rest_api_permissions";
		$permissions = get_user_meta( $user_id, $key, true );

		if ( ! $permissions && isset( $item['rest_api_permissions'] ) ) {
			$permissions = $item['rest_api_permissions'];
			update_user_meta( $user_id, $key, $permissions );
		}

		if ( 'read' === $permissions ) {
			return 'read';
		}

		return 'write';
	}

	/**
	 * Gets the configured API types for the password.
	 *
	 * @param int   $user_id The user id.
	 * @param array $item    The password details.
	 *
	 * @return string[]
	 */
	protected function get_enabled_for( $user_id, $item ) {
		$key         = "_{$item['uuid']}_enabled_for";
		$enabled_for = get_user_meta( $user_id, $key, true );

		if ( ! $enabled_for && isset( $item['enabled_for'] ) ) {
			$enabled_for = $item['enabled_for'];
			update_user_meta( $user_id, $key, $enabled_for );
		}

		if ( ! $enabled_for || ! is_array( $enabled_for ) ) {
			return array( 'rest-api', 'xml-rpc' );
		}

		return $enabled_for;
	}
}
