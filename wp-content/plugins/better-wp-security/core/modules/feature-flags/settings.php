<?php

namespace iThemesSecurity\FeatureFlags;

use iThemesSecurity\Config_Settings;

final class Settings extends Config_Settings {
	public function show_ui(): bool {
		return \ITSEC_Lib_Feature_Flags::show_ui();
	}

	public function get_settings_schema() {
		$schema = parent::get_settings_schema();

		foreach ( \ITSEC_Lib_Feature_Flags::get_available_flags() as $flag => $config ) {
			list( $reason_code ) = \ITSEC_Lib_Feature_Flags::get_reason( $flag );

			$schema['properties']['enabled']['items']['enum'][]      = $flag;
			$schema['properties']['enabled']['items']['enumNames'][] = $config['title'];
			$schema['uiSchema']['enabled']['ui:enumDescriptions'][]  = $this->prepare_flag_description( $flag );

			if ( $reason_code === 'constant' ) {
				$schema['uiSchema']['enabled']['ui:enumDisabled'][] = $flag;
			}
		}

		return $schema;
	}

	private function prepare_flag_description( $flag ) {
		$config = \ITSEC_Lib_Feature_Flags::get_flag_config( $flag );
		list( $reason_code, $reason ) = \ITSEC_Lib_Feature_Flags::get_reason( $flag );

		$description = $config['description'];

		if ( $description ) {
			$description .= '<br>';
		}

		if ( $reason_code === 'setting' ) {
			return $description;
		}

		$description .= '<em>';

		if ( in_array( $flag, $this->settings['enabled'], true ) ) {
			$description .= sprintf( __( 'Enabled reason: %s' ), $reason );
		} else {
			$description .= sprintf( __( 'Disabled reason: %s' ), $reason );
		}

		$description .= '</em>';

		return $description;
	}

	public function prepare_for_rest() {
		$settings = parent::prepare_for_rest();

		$settings['enabled'] = [];

		foreach ( \ITSEC_Lib_Feature_Flags::get_available_flags() as $flag => $config ) {
			if ( \ITSEC_Lib_Feature_Flags::is_enabled( $flag ) ) {
				$settings['enabled'][] = $flag;
			}
		}

		return $settings;
	}

	public function prepare_from_rest( $settings ) {
		foreach ( $settings['enabled'] ?? [] as $i => $flag ) {
			$is_enabled = \ITSEC_Lib_Feature_Flags::is_enabled( $flag );

			if ( $is_enabled && ! in_array( $flag, $this->settings['enabled'], true ) ) {
				unset( $settings['enabled'][ $i ] );
			}
		}

		foreach ( \ITSEC_Lib_Feature_Flags::get_available_flags() as $flag => $config ) {
			if (
				\ITSEC_Lib_Feature_Flags::is_enabled( $flag ) &&
				! in_array( $flag, $this->settings['enabled'], true ) &&
				! in_array( $flag, $settings['enabled'], true )
			) {
				$settings['opt_outs'][] = $flag;
			}
		}

		return $settings;
	}
}

\ITSEC_Modules::register_settings( new Settings( \ITSEC_Modules::get_config( 'feature-flags' ) ) );
