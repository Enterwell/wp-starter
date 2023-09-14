<?php

namespace iThemesSecurity\Lib\Site_Types\Question;

use iThemesSecurity\Lib\Site_Types\Answer_Handler;
use iThemesSecurity\Lib\Site_Types\Questions_Provider;
use iThemesSecurity\Lib\Site_Types\Responds;
use iThemesSecurity\Lib\Site_Types\Site_Type;
use iThemesSecurity\Lib\Site_Types\Templated_Question;
use iThemesSecurity\Lib\Site_Types\Templating_Site_Type;
use iThemesSecurity\Lib\Site_Types\Templating_Site_Type_Adapter;

final class Login_Security_Question_Pack implements Questions_Provider {

	/** @var Templating_Site_Type */
	private $site_type;

	/**
	 * Login_Security_Question_Pack constructor.
	 *
	 * @param Site_Type $site_type
	 */
	public function __construct( Site_Type $site_type ) { $this->site_type = new Templating_Site_Type_Adapter( $site_type ); }

	public function get_questions(): array {
		$questions = [];

		if ( \ITSEC_Core::is_pro() ) {
			$questions[] = new class ( $this->site_type ) extends Templated_Question implements Responds {
				public function get_id(): string {
					return self::END_USERS_TWO_FACTOR;
				}

				public function get_answer_schema(): array {
					return [
						'type'     => 'boolean',
						'title'    => __( 'Yes, require Two-Factor for these users.', 'better-wp-security' ),
						'default'  => false,
						'uiSchema' => [
							'ui:widget' => 'ToggleWidget',
						],
					];
				}

				protected function get_prompt_fallback(): string {
					return __( 'Do you want to secure your user accounts with two-factor authentication?', 'better-wp-security' );
				}

				public function get_description(): string {
					return __( 'Securing users with two-factor authentication will require them to enter a security code along with their username and password to log in. Two-Factor authentication is the strongest way to prevent automated bot attacks.', 'better-wp-security' );
				}

				public function respond( Answer_Handler $handler ) {
					if ( $handler->get_answer() !== true ) {
						return;
					}

					$handler->enable_module( 'two-factor' );
					$handler->enable_module( 'passwordless-login' );

					$user_groups = [];

					if ( $handler->has_answered( self::SELECT_END_USERS ) ) {
						$user_groups = $handler->get_previous( self::SELECT_END_USERS )->get_user_groups();
					}

					foreach ( $user_groups as $user_group ) {
						$handler->enable_setting_for( $user_group, 'two-factor', 'remember_group' );
						$handler->enable_setting_for( $user_group, 'passwordless-login', '2fa_bypass_group' );
					}

					if ( $handler->has_answered( self::SELECT_CLIENTS ) ) {
						$user_groups = array_merge( $user_groups, $handler->get_previous( self::SELECT_CLIENTS )->get_user_groups() );
					}

					if ( $handler->has_answered( self::SELECT_SITE_ADMINS ) ) {
						$user_groups = array_merge( $user_groups, $handler->get_previous( self::SELECT_SITE_ADMINS )->get_user_groups() );
					}

					foreach ( $user_groups as $user_group ) {
						$handler->enable_setting_for( $user_group, 'two-factor', 'protect_user_group' );
					}

					foreach ( \ITSEC_Lib_Canonical_Roles::get_canonical_roles( false ) as $canonical ) {
						$handler->enable_setting_for_canonical( $canonical, 'two-factor', 'protect_user_group' );

						if ( 'administrator' !== $canonical ) {
							$handler->enable_setting_for_canonical( $canonical, 'two-factor', 'remember_group' );
							$handler->enable_setting_for_canonical( $canonical, 'passwordless-login', '2fa_bypass_group' );
						}
					}
				}
			};
		}

		$questions[] = new class ( $this->site_type ) extends Templated_Question implements Responds {
			public function get_id(): string {
				return self::END_USERS_PASSWORD_POLICY;
			}

			public function get_answer_schema(): array {
				return [
					'type'     => 'boolean',
					'title'    => __( 'Yes, enforce a password policy for these users.', 'better-wp-security' ),
					'default'  => false,
					'uiSchema' => [
						'ui:widget' => 'ToggleWidget',
					],
				];
			}

			protected function get_prompt_fallback(): string {
				return __( 'Do you want to secure your user accounts with a password policy?', 'better-wp-security' );
			}

			public function get_description(): string {
				return __( 'When you secure your users with a password policy, iThemes Security will require them to create a strong password that hasn’t already been compromised. ', 'better-wp-security' );
			}

			public function respond( Answer_Handler $handler ) {
				if ( $handler->get_answer() !== true ) {
					return;
				}

				if ( \ITSEC_Modules::is_available( 'passwordless-login' ) ) {
					$handler->enable_module( 'passwordless-login' );
				}

				$user_groups = [];

				if ( $handler->has_answered( self::SELECT_END_USERS ) ) {
					$user_groups = $handler->get_previous( self::SELECT_END_USERS )->get_user_groups();
				}

				if ( \ITSEC_Modules::is_available( 'passwordless-login' ) ) {
					foreach ( $user_groups as $user_group ) {
						$handler->enable_setting_for( $user_group, 'passwordless-login', 'group' );
					}
				}

				if ( $handler->has_answered( self::SELECT_CLIENTS ) ) {
					$user_groups = array_merge( $user_groups, $handler->get_previous( self::SELECT_CLIENTS )->get_user_groups() );
				}

				if ( $handler->has_answered( self::SELECT_SITE_ADMINS ) ) {
					$user_groups = array_merge( $user_groups, $handler->get_previous( self::SELECT_SITE_ADMINS )->get_user_groups() );
				}

				foreach ( $user_groups as $user_group ) {
					$handler->enable_setting_for( $user_group, 'password-requirements', 'requirement_settings.strength.group' );
					$handler->enable_setting_for( $user_group, 'password-requirements', 'requirement_settings.hibp.group' );
				}

				foreach ( \ITSEC_Lib_Canonical_Roles::get_canonical_roles( false ) as $canonical ) {
					$handler->enable_setting_for_canonical( $canonical, 'password-requirements', 'requirement_settings.strength.group' );
					$handler->enable_setting_for_canonical( $canonical, 'password-requirements', 'requirement_settings.hibp.group' );

					if ( \ITSEC_Modules::is_available( 'passwordless-login' ) && 'administrator' !== $canonical ) {
						$handler->enable_setting_for_canonical( $canonical, 'passwordless-login', 'group' );
					}
				}
			}
		};

		return $questions;
	}
}
