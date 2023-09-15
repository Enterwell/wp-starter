<?php

namespace iThemesSecurity\Lib\Site_Types;

final class Defaults {
	const DEFAULT = [
		'woocommerce/woocommerce.php'                       => [
			'type'      => Site_Type::ECOMMERCE,
			'questions' => [
				Question::SELECT_END_USERS => [ 'customer', 'subscriber' ],
			],
		],
		'restrict-content/restrictcontent.php'              => [
			'type'      => Site_Type::ECOMMERCE,
			'questions' => [
				Question::SELECT_END_USERS => [ 'subscriber' ],
			],
		],
		'restrict-content-pro/restrict-content-pro.php'     => [
			'type'      => Site_Type::ECOMMERCE,
			'questions' => [
				Question::SELECT_END_USERS => [ 'subscriber' ],
			],
		],
		'easy-digital-downloads/easy-digital-downloads.php' => [
			'type'      => Site_Type::ECOMMERCE,
			'questions' => [
				Question::SELECT_END_USERS => [ 'subscriber' ],
			],
		],
		'lifterlms/lifterlms.php'                           => [
			'type'      => Site_Type::ECOMMERCE,
			'questions' => [
				Question::SELECT_END_USERS => [ 'student', 'subscriber' ],
			],
		],
		'paid-memberships-pro/paid-memberships-pro.php'     => [
			'type'      => Site_Type::ECOMMERCE,
			'questions' => [
				Question::SELECT_END_USERS => [ 'subscriber' ],
			],
		],
		'members/members.php'                               => [
			'type'      => Site_Type::ECOMMERCE,
			'questions' => [
				Question::SELECT_END_USERS => [ 'subscriber' ],
			],
		],
		'memberpress/memberpress.php'                       => [
			'type'      => Site_Type::ECOMMERCE,
			'questions' => [
				Question::SELECT_END_USERS => [ 'subscriber' ],
			],
		],
		'ultimate-member/ultimate-member.php'               => [
			'type'      => Site_Type::ECOMMERCE,
			'questions' => [
				Question::SELECT_END_USERS => [ 'subscriber' ],
			],
		],
		'give/give.php'                                     => [
			'type'      => Site_Type::NON_PROFIT,
			'questions' => [
				Question::SELECT_END_USERS => [ 'give_donor', 'subscriber' ],
			],
		],
		'charitable/charitable.php'                         => [
			'type'      => Site_Type::NON_PROFIT,
			'questions' => [
				Question::SELECT_END_USERS => [ 'donor', 'subscriber' ],
			],
		],
		'paypal-donations/paypal-donations.php'             => [
			'type'      => Site_Type::NON_PROFIT,
			'questions' => [
				Question::SELECT_END_USERS => [ 'subscriber' ],
			],
		],
		'buddypress/bp-loader.php'                          => [
			'type'      => Site_Type::NETWORK,
			'questions' => [
				Question::SELECT_END_USERS => [ 'give_donor', 'subscriber' ],
			],
		],
		'bbpress/bbpress.php'                               => [
			'type'      => Site_Type::NETWORK,
			'questions' => [
				Question::SELECT_END_USERS => [ 'bbp_spectator', 'bbp_participant', 'subscriber' ],
			],
		],
	];

	/** @var array */
	private $config;

	/**
	 * Gets the suggested site type based on the installed plugins.
	 *
	 * @return string
	 */
	public function get_suggested_site_type(): string {
		if ( $config = \ITSEC_Lib::first( $this->get_config() ) ) {
			return $config['type'];
		}

		return '';
	}

	/**
	 * Gets the default value for a question.
	 *
	 * @param string $question_id The question id.
	 *
	 * @retrun mixed|null The default if available, otherwise null.
	 */
	public function get_default_for_question( string $question_id ) {
		$default = null;

		foreach ( $this->get_config() as $config ) {
			if ( isset( $config['questions'][ $question_id ] ) ) {
				$plugin_default = $config['questions'][ $question_id ];

				if ( is_array( $plugin_default ) && is_array( $default ) ) {
					$default = array_merge( $default, $plugin_default );
				} else {
					$default = $plugin_default;
				}
			}
		}

		if ( is_array( $default ) ) {
			$default = \ITSEC_Lib::non_scalar_array_unique( $default, true );
		}

		return $default;
	}

	/**
	 * Gets the active config.
	 *
	 * @return array
	 */
	private function get_config(): array {
		if ( ! $this->config ) {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$config       = apply_filters( 'itsec_site_type_default_config', self::DEFAULT );
			$this->config = array_filter( $config, 'is_plugin_active', ARRAY_FILTER_USE_KEY );
		}

		return $this->config;
	}
}
