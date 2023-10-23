<?php

namespace iThemesSecurity\Lib\Tools;

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Lib\Result;

final class Tools_Runner implements Runnable {

	/** @var Tools_Registry */
	private $registry;

	/**
	 * Runner constructor.
	 *
	 * @param Tools_Registry $registry
	 */
	public function __construct( Tools_Registry $registry ) { $this->registry = $registry; }

	public function run() {
		/**
		 * Fires when tools should be registered.
		 *
		 * @param Tools_Registry $registry
		 */
		do_action( 'itsec_register_tools', $this->registry );

		foreach ( $this->registry->get_tools() as $tool ) {
			if ( $schedule = $tool->get_schedule() ) {
				add_action( "itsec_scheduled_{$schedule}", function () use ( $tool ) {
					$this->run_tool( $tool );
				} );
			}

			if ( $this->is_enabled( $tool ) ) {
				$this->run_tool( $tool );
			}
		}
	}

	/**
	 * Runs a tool.
	 *
	 * @param Tool  $tool The tool to run.
	 * @param array $form The form data to provide to the tool.
	 *
	 * @return Result
	 */
	public function run_tool( Tool $tool, array $form = [] ): Result {
		if ( ! $tool->is_available() ) {
			return Result::error( new \WP_Error(
				'itsec.tools.run.not-available',
				__( 'This tool cannot run because it isn‘t available.', 'better-wp-security' )
			) );
		}

		if ( ! $this->is_condition_valid( $tool ) ) {
			return Result::error( new \WP_Error(
				'itsec.tools.run.condition-invalid',
				__( 'This tool cannot run because its condition is not valid.', 'better-wp-security' )
			) );
		}

		if ( $tool->get_form() ) {
			$valid = rest_validate_value_from_schema( $form, $tool->get_form() );

			if ( is_wp_error( $valid ) ) {
				return Result::error( $valid );
			}

			$sanitized = rest_sanitize_value_from_schema( $form, $tool->get_form() );

			if ( is_wp_error( $sanitized ) ) {
				return Result::error( $sanitized );
			}

			return $tool->run( $form );
		}

		return $tool->run();
	}

	/**
	 * Checks if the condition for the Tool is valid.
	 *
	 * @param Tool $tool
	 *
	 * @return bool
	 */
	public function is_condition_valid( Tool $tool ): bool {
		if ( ! $condition = $tool->get_condition() ) {
			return true;
		}

		if ( isset( $condition['active-modules'] ) ) {
			$active = array_filter( $condition['active-modules'], 'ITSEC_Modules::is_active' );

			if ( count( $active ) !== count( $condition['active-modules'] ) ) {
				return false;
			}
		}

		if ( isset( $condition['settings'] ) ) {
			foreach ( $condition['settings'] as $module => $schema ) {
				$settings = \ITSEC_Modules::get_settings( $module );

				if ( is_wp_error( rest_validate_value_from_schema( $settings, $schema ) ) ) {
					return false;
				}

				if ( is_wp_error( rest_sanitize_value_from_schema( $settings, $schema ) ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Checks if a toggleable tool is enabled.
	 *
	 * @param Tool $tool
	 *
	 * @return bool
	 */
	public function is_enabled( Tool $tool ): bool {
		$enabled = \ITSEC_Modules::get_setting( 'global', 'enabled_tools' );

		return $tool->is_toggleable() && in_array( $tool->get_slug(), $enabled, true );
	}

	/**
	 * Enables a tool.
	 *
	 * @param Tool $tool
	 *
	 * @return true|\WP_Error
	 */
	public function enable_tool( Tool $tool ) {
		if ( ! $tool->is_toggleable() ) {
			return new \WP_Error(
				'itsec.tools.enable-non-toggleable',
				__( 'This tool cannot be enabled because it isn‘t a toggleable tool.', 'better-wp-security' )
			);
		}

		$enabled = \ITSEC_Modules::get_setting( 'global', 'enabled_tools' );

		if ( in_array( $tool->get_slug(), $enabled, true ) ) {
			return true;
		}

		$enabled[] = $tool->get_slug();
		$updated   = \ITSEC_Modules::set_setting( 'global', 'enabled_tools', $enabled );

		$error = \ITSEC_Lib::updated_settings_to_wp_error( $updated );

		if ( is_wp_error( $error ) ) {
			return $error;
		}

		return true;
	}

	/**
	 * Disables a tool.
	 *
	 * @param Tool $tool
	 *
	 * @return true|\WP_Error
	 */
	public function disable_tool( Tool $tool ) {
		if ( ! $tool->is_toggleable() ) {
			return new \WP_Error(
				'itsec.tools.disable-non-toggleable',
				__( 'This tool cannot be disabled because it isn‘t a toggleable tool.', 'better-wp-security' )
			);
		}

		$enabled = \ITSEC_Modules::get_setting( 'global', 'enabled_tools' );
		$found   = array_search( $tool->get_slug(), $enabled, true );

		if ( $found === false ) {
			return true;
		}

		unset( $enabled[ $found ] );
		$updated = \ITSEC_Modules::set_setting( 'global', 'enabled_tools', $enabled );
		$error   = \ITSEC_Lib::updated_settings_to_wp_error( $updated );

		if ( is_wp_error( $error ) ) {
			return $error;
		}

		return true;
	}
}
