<?php

namespace iThemesSecurity\Lib\Site_Types;

use iThemesSecurity\Exception\Invalid_Argument_Exception;
use iThemesSecurity\User_Groups\User_Group;

final class Answer_Handler implements Answer_Details {

	/** @var Question */
	private $question;

	/** @var mixed */
	private $answer;

	/** @var User_Group[] */
	private $user_groups = [];

	/** @var array */
	private $user_group_settings = [];

	/** @var array */
	private $canonical_group_substitutions = [];

	/** @var array */
	private $settings = [];

	/** @var array */
	private $modules = [];

	/** @var Answer_Details[] */
	private $previous;

	/**
	 * Answer_Handler constructor.
	 *
	 * @param Question       $question
	 * @param mixed          $answer
	 * @param Answer_Details ...$previous
	 */
	public function __construct( Question $question, $answer, Answer_Details ...$previous ) {
		$this->question = $question;
		$this->answer   = $answer;
		$this->previous = $previous;
	}

	public function get_question(): Question {
		return $this->question;
	}

	public function get_answer() {
		return $this->answer;
	}

	/**
	 * Prefills a user group with the given description.
	 *
	 * @param User_Group $user_group A non-persisted user group.
	 *
	 * @return self
	 */
	public function create_user_group( User_Group $user_group ): self {
		foreach ( $this->user_groups as $i => $other_user_group ) {
			if ( $other_user_group->identical( $user_group ) || $other_user_group->equals( $user_group ) ) {
				$this->user_groups[ $i ] = $user_group;

				return $this;
			}
		}

		$this->user_groups[] = $user_group;

		return $this;
	}

	/**
	 * Enables a setting for the given user group.
	 *
	 * @param User_Group $user_group
	 * @param string     $module
	 * @param string     $setting
	 *
	 * @return self
	 */
	public function enable_setting_for( User_Group $user_group, string $module, string $setting ): self {
		$this->user_group_settings[ $user_group->get_id() ][ $module ][] = $setting;

		return $this;
	}

	/**
	 * Enables a setting for a default canonical user group.
	 *
	 * @param string $role
	 * @param string $module
	 * @param string $setting
	 *
	 * @return $this
	 */
	public function enable_setting_for_canonical( string $role, string $module, string $setting ): self {
		if ( ! in_array( $role, \ITSEC_Lib_Canonical_Roles::get_canonical_roles() ) ) {
			throw new Invalid_Argument_Exception( "'$role' is not a valid canonical role." );
		}

		$this->user_group_settings[ $role ][ $module ][] = $setting;

		return $this;
	}

	/**
	 * Enables the selected module.
	 *
	 * @param string $module
	 *
	 * @return $this
	 */
	public function enable_module( string $module ): self {
		$this->modules[] = $module;

		return $this;
	}

	/**
	 * Prefills a setting value.
	 *
	 * @param string $module  The module id.
	 * @param string $setting The setting name.
	 * @param mixed  $value   The value to use.
	 *
	 * @return self
	 */
	public function set_setting( string $module, string $setting, $value ): self {
		$this->settings[ $module ][ $setting ] = $value;

		return $this;
	}

	public function get_user_groups(): array {
		return $this->user_groups;
	}

	public function get_user_group_settings(): array {
		return $this->user_group_settings;
	}

	public function get_settings(): array {
		return $this->settings;
	}

	public function get_enabled_modules(): array {
		return $this->modules;
	}

	/**
	 * Substitutes the default created canonical group with the given user group.
	 *
	 * @param string          $role       The canonical role. For example, 'subscriber'.
	 * @param User_Group|null $user_group The user group to substitute.
	 *                                    Or `null` to prevent the group from being created.
	 *
	 * @return $this
	 */
	public function substitute_canonical_user_group( string $role, User_Group $user_group = null ): self {
		if ( ! in_array( $role, \ITSEC_Lib_Canonical_Roles::get_canonical_roles() ) ) {
			throw new Invalid_Argument_Exception( "'$role' is not a valid canonical role." );
		}

		$this->canonical_group_substitutions[ $role ] = $user_group ? $user_group->get_id() : null;

		return $this;
	}

	public function get_canonical_user_group_substitutions(): array {
		return $this->canonical_group_substitutions;
	}

	/**
	 * Checks if the given question has been answered previously.
	 *
	 * @param string $question_id
	 *
	 * @return bool
	 */
	public function has_answered( string $question_id ): bool {
		foreach ( $this->previous as $previous ) {
			if ( $previous->get_question()->get_id() === $question_id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Gets the answer to a previous question.
	 *
	 * @param string $question_id
	 *
	 * @return Answer_Details
	 */
	public function get_previous( string $question_id ): Answer_Details {
		foreach ( $this->previous as $previous ) {
			if ( $previous->get_question()->get_id() === $question_id ) {
				return $previous;
			}
		}

		throw new Invalid_Argument_Exception( "No question found for '{$question_id}'." );
	}
}
