<?php

namespace iThemesSecurity\Lib\Site_Types;

use iThemesSecurity\User_Groups\User_Group;

final class Answered_Question implements Answer_Details {

	/** @var Question */
	private $question;

	/** @var mixed */
	private $answer;

	/** @var User_Group[] */
	private $user_groups;

	/** @var array */
	private $user_group_settings;

	/** @var array */
	private $canonical_group_substitutions;

	/** @var string[] */
	private $modules;

	/** @var array */
	private $settings;

	/**
	 * Answered_Question constructor.
	 *
	 * @param Question     $question
	 * @param mixed        $answer
	 * @param User_Group[] $user_groups
	 * @param array        $user_group_settings
	 * @param string[]     $canonical_group_substitutions
	 * @param string[]     $modules
	 * @param array        $settings
	 */
	public function __construct(
		Question $question,
		$answer,
		array $user_groups = [],
		array $user_group_settings = [],
		array $canonical_group_substitutions = [],
		array $modules = [],
		array $settings = []
	) {
		$this->question                      = $question;
		$this->answer                        = $answer;
		$this->user_groups                   = $user_groups;
		$this->user_group_settings           = $user_group_settings;
		$this->canonical_group_substitutions = $canonical_group_substitutions;
		$this->modules                       = $modules;
		$this->settings                      = $settings;
	}

	public static function from_answer_details( Answer_Details $details ): self {
		return new self(
			$details->get_question(),
			$details->get_answer(),
			$details->get_user_groups(),
			$details->get_user_group_settings(),
			$details->get_canonical_user_group_substitutions(),
			$details->get_enabled_modules(),
			$details->get_settings()
		);
	}

	public function get_question(): Question {
		return $this->question;
	}

	public function get_answer() {
		return $this->answer;
	}

	public function get_user_groups(): array {
		return $this->user_groups;
	}

	public function get_user_group_settings(): array {
		return $this->user_group_settings;
	}

	public function get_canonical_user_group_substitutions(): array {
		return $this->canonical_group_substitutions;
	}

	public function get_settings(): array {
		return $this->settings;
	}

	public function get_enabled_modules(): array {
		return $this->modules;
	}
}
