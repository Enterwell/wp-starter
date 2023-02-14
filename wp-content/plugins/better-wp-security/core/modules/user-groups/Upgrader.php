<?php

namespace iThemesSecurity\User_Groups;

use iThemesSecurity\User_Groups\Module\Settings;
use iThemesSecurity\User_Groups\Repository\Repository;
use ITSEC_Lib_Canonical_Roles;

final class Upgrader {

	/** @var Repository */
	private $user_groups;

	/** @var Settings */
	private $settings;

	/**
	 * Upgrader constructor.
	 *
	 * @param Repository $user_groups
	 * @param Settings   $settings
	 */
	public function __construct( Repository $user_groups, Settings $settings ) {
		$this->user_groups = $user_groups;
		$this->settings    = $settings;
	}

	/**
	 * Upgrade an existing setting to a user group.
	 *
	 * @param string   $label     Label to give the user group if a new one has to be created.
	 * @param callable $configure Callable to configure the user group.
	 *
	 * @return User_Group Either the newly created user group, or an existing user group that was an exact match.
	 */
	public function find_or_create( $label, callable $configure ) {
		$user_group = new User_Group( $this->user_groups->next_id() );
		$configure( $user_group );

		foreach ( $this->user_groups->all() as $maybe_group ) {
			if ( $maybe_group->equals( $user_group ) ) {
				return $maybe_group;
			}
		}

		$user_group->set_label( $label );
		$this->user_groups->persist( $user_group );

		return $user_group;
	}

	/**
	 * Upgrade a min role based setting to a group based setting.
	 *
	 * @param string $min_role The minimum canonical role.
	 *
	 * @return string[] The list of user group IDs to store.
	 */
	public function upgrade_from_min_role( $min_role ) {
		$groups = [];

		foreach ( ITSEC_Lib_Canonical_Roles::get_canonical_roles_of_at_least( $min_role ) as $role ) {
			if ( 'super-admin' === $role ) {
				continue;
			}

			$groups[] = $this->settings->get_default_group_id( $role, true );
		}

		if ( 'subscriber' === $min_role ) {
			$groups[] = 'everybody-else';
		}

		return $groups;
	}
}
