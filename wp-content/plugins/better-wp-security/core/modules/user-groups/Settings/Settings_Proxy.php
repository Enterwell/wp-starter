<?php

namespace iThemesSecurity\User_Groups;

final class Settings_Proxy {

	/**
	 * Check if a matchable has the given setting enabled.
	 *
	 * @param Matchable             $matchable    The matchable to check for.
	 * @param Settings_Registration $registration The setting to check.
	 *
	 * @return bool
	 */
	public function is_enabled( Matchable $matchable, Settings_Registration $registration ) {
		$value = \ITSEC_Lib::array_get( \ITSEC_Modules::get_settings( $registration->get_module() ), $registration->get_setting() );

		return in_array( $matchable->get_id(), $value, true );
	}

	/**
	 * Set whether a setting is enabled.
	 *
	 * @param Matchable             $matchable    The matchable to enable/disable the setting for.
	 * @param Settings_Registration $registration The setting.
	 * @param bool                  $enabled      Whether the setting should be enabled.
	 *
	 * @return \WP_Error|null Null on success, WP_Error on error.
	 */
	public function set_enabled( Matchable $matchable, Settings_Registration $registration, $enabled = true ) {
		$settings = \ITSEC_Modules::get_settings( $registration->get_module() );
		$current  = \ITSEC_Lib::array_get( $settings, $registration->get_setting() );

		if ( $enabled && ! in_array( $matchable->get_id(), $current, true ) ) {
			$current[] = $matchable->get_id();
		} elseif ( ! $enabled && ( $i = array_search( $matchable->get_id(), $current, true ) ) !== false ) {
			unset( $current[ $i ] );
		}

		$settings = \ITSEC_Lib::array_set( $settings, $registration->get_setting(), $current );

		$updated = \ITSEC_Modules::set_settings( $registration->get_module(), $settings );

		return \ITSEC_Lib::updated_settings_to_wp_error( $updated );
	}
}
