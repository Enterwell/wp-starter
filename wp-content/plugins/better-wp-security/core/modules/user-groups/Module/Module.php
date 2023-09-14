<?php

namespace iThemesSecurity\User_Groups\Module;

use iThemesSecurity\Contracts\Import_Export_Source;
use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Import_Export\Export\Export;
use iThemesSecurity\Import_Export\Import\Import_Context;
use iThemesSecurity\Import_Export\Import\Transformation;
use iThemesSecurity\Lib\Result;
use iThemesSecurity\Module_Config;
use iThemesSecurity\User_Groups\Everybody_Else;
use iThemesSecurity\User_Groups\Repository\Repository;
use iThemesSecurity\User_Groups\Settings_Proxy;
use iThemesSecurity\User_Groups\Settings_Registration;
use iThemesSecurity\User_Groups\Settings_Registry;
use iThemesSecurity\User_Groups\User_Group;

class Module implements Runnable, Import_Export_Source {

	/** @var Repository */
	private $repository;

	/** @var Settings_Registry */
	private $settings_registry;

	/** @var Settings_Proxy */
	private $settings_proxy;

	/**
	 * Active constructor.
	 *
	 * @param Repository        $repository
	 * @param Settings_Registry $settings_registry
	 * @param Settings_Proxy    $settings_proxy
	 */
	public function __construct( Repository $repository, Settings_Registry $settings_registry, Settings_Proxy $settings_proxy ) {
		$this->repository        = $repository;
		$this->settings_registry = $settings_registry;
		$this->settings_proxy    = $settings_proxy;
	}

	public function run() {
		add_action( 'itsec_initialized', [ $this, 'trigger_setting_registration' ] );
		add_filter( 'map_meta_cap', [ $this, 'map_meta_cap' ], 10, 4 );
		add_action( 'itsec_create_user_group', [ $this, 'initialize_settings' ], 10, 2 );
		add_action( 'itsec_change_admin_user_id', [ $this, 'on_change_admin_user_id' ], 10, 3 );
	}

	public function trigger_setting_registration() {
		foreach ( \ITSEC_Modules::get_config_list() as $config ) {
			if ( ! $user_groups = $config->get_user_groups() ) {
				continue;
			}

			foreach ( $user_groups as $setting => $user_group ) {
				$this->settings_registry->register( new Settings_Registration(
					$config->get_id(),
					$setting,
					$user_group['type'],
					static function () use ( $config, $setting ) {
						$user_group = $config->translate( Module_Config::T_USER_GROUPS )
						                     ->get_user_groups()[ $setting ];

						return [
							'title'       => $user_group['title'],
							'description' => $user_group['description'],
						];
					}
				) );
			}
		}

		do_action( 'itsec_register_user_group_settings', $this->settings_registry );
	}

	/**
	 * Map meta capabilities.
	 *
	 * @param string[] $caps
	 * @param string   $cap
	 * @param int      $user_id
	 * @param array    $args
	 *
	 * @return string[]
	 */
	public function map_meta_cap( $caps, $cap, $user_id, $args ) {
		switch ( $cap ) {
			case 'itsec_list_user_groups':
			case 'itsec_create_user_groups':
			case 'itsec_edit_user_groups':
				return [ \ITSEC_Core::get_required_cap() ];
			case 'itsec_read_user_group':
			case 'itsec_edit_user_group':
			case 'itsec_delete_user_group':
				if ( empty( $args[0] ) || ! is_string( $args[0] ) || ! $this->repository->has( $args[0] ) ) {
					return [ 'do_not_allow' ];
				}

				return [ \ITSEC_Core::get_required_cap() ];
			default:
				return $caps;
		}
	}

	/**
	 * Initialize a new user group's settings to use the configuration for the Everybody Else group.
	 *
	 * @param User_Group $user_group
	 * @param array      $args
	 */
	public function initialize_settings( User_Group $user_group, array $args = [] ) {
		if ( \ITSEC_Core::is_importing() || ! empty( $args['is_default'] ) ) {
			// During an import, there is no need to initialize these groups as "new" since we'll
			// be importing their settings shortly.
			return;
		}

		foreach ( $this->settings_registry->get_settings() as $registration ) {
			$value = $this->settings_proxy->is_enabled( new Everybody_Else( $this->repository ), $registration );

			if ( $value ) {
				$this->settings_proxy->set_enabled( $user_group, $registration );
			}
		}
	}

	/**
	 * Fires when the "1" user ID has been changed.
	 *
	 * @param int      $new_id   The new user ID.
	 * @param \WP_User $old_user The old user object.
	 */
	public function on_change_admin_user_id( $new_id, $old_user ) {
		if ( ! $new_user = get_userdata( $new_id ) ) {
			return;
		}

		foreach ( $this->repository->all() as $group ) {
			foreach ( $group->get_users() as $user ) {
				if ( $user->ID === 1 ) {
					$group->add_user( $new_user );
					$group->remove_user( $old_user );
					$this->repository->persist( $group );
				}
			}
		}
	}

	public function get_export_slug(): string {
		return 'user-groups';
	}

	public function get_export_title(): string {
		return __( 'User Groups', 'better-wp-security' );
	}

	public function get_export_description(): string {
		return __( 'List of created User Groups', 'better-wp-security' );
	}

	public function get_export_options_schema(): array {
		return [];
	}

	public function get_export_schema(): array {
		return [
			'type'  => 'array',
			'items' => [
				'type'       => 'object',
				'properties' => [
					'id'        => [
						'type' => 'string',
					],
					'label'     => [
						'type' => 'string',
					],
					'users'     => [
						'type'  => 'array',
						'items' => [
							'$ref' => '#/definitions/user',
						],
					],
					'roles'     => [
						'type'  => 'array',
						'items' => [
							'$ref' => '#/definitions/role',
						],
					],
					'canonical' => [
						'type'  => 'array',
						'items' => [
							'type' => 'string',
						],
					],
					'min_role'  => [
						'type' => 'string',
					],
				],
			],
		];
	}

	public function get_transformations(): array {
		return [
			new class implements Transformation {
				public function transform( Export $export, Import_Context $context ): Export {
					$data = $export->get_data( 'user-groups' );

					foreach ( $data as &$user_group ) {
						$user_group['users'] = $context->map_user_list( $user_group['users'] );
						$user_group['roles'] = $context->map_role_list( $user_group['roles'] );
					}

					return $export->with_data( 'user-groups', $data );
				}

				public function get_user_paths(): array {
					return [ '*.users' ];
				}

				public function get_role_paths(): array {
					return [ '*.roles' ];
				}
			}
		];
	}

	public function export( $options ): Result {
		return Result::success( array_map( static function ( User_Group $group ) {
			return [
				'id'        => $group->get_id(),
				'label'     => $group->get_label(),
				'users'     => array_map( [ Export::class, 'format_user' ], $group->get_users() ),
				'roles'     => array_map( [ Export::class, 'format_role' ], $group->get_roles() ),
				'canonical' => $group->get_canonical_roles(),
				'min_role'  => $group->get_min_role(),
			];
		}, $this->repository->all() ) );
	}

	public function import( Export $from, Import_Context $context ): Result {
		array_map( [ $this->repository, 'delete' ], $this->repository->all() );

		$result = Result::success();

		foreach ( $from->get_data( $this->get_export_slug() ) as $config ) {
			$user_group = new User_Group( $config['id'] );
			$user_group->set_label( $config['label'] );
			$user_group->set_canonical_roles( $config['canonical'] );
			$user_group->set_min_role( $config['min_role'] );

			foreach ( $config['roles'] as $role ) {
				$user_group->add_role( $role['slug'] );
			}

			foreach ( $config['users'] as $user ) {
				if ( $user = get_userdata( $user['id'] ) ) {
					$user_group->add_user( $user );
				}
			}

			try {
				$this->repository->persist( $user_group );
			} catch ( \Exception $e ) {
				$result->add_warning_message( sprintf(
					__( 'Could not import \'%1$s\' User Group: %2$s', 'better-wp-security' ),
					$config['label'],
					$e->getMessage()
				) );
			}
		}

		return $result;
	}
}
