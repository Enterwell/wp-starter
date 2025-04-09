<?php

namespace iThemesSecurity\Lib;

interface Password_Requirement {
	/**
	 * Gets the reason code for the password requirement.
	 *
	 * @return string
	 */
	public function get_code(): string;

	/**
	 * Gets the module responsible for the password requirement.
	 *
	 * @return string
	 */
	public function get_module(): string;

	/**
	 * Gets the title for the password requirement.
	 *
	 * @return string
	 */
	public function get_title(): string;

	/**
	 * Gets the description for the password requirement.
	 *
	 * @return string
	 */
	public function get_description(): string;

	/**
	 * Checks if the user must change their password.
	 *
	 * @param \WP_User $user     The user object.
	 * @param array    $settings The selected settings.
	 *
	 * @return bool
	 */
	public function is_password_change_required( \WP_User $user, array $settings ): bool;

	/**
	 * Evaluates a password.
	 *
	 * @param string             $password The raw password.
	 * @param \WP_User|\stdClass $user     The user object. May be an \stdClass
	 *                                     if a user is being edited or created.
	 *
	 * @return mixed|\WP_Error The evaluation, or a WP_Error instance if the password could not be evaluated.
	 */
	public function evaluate( string $password, $user );

	/**
	 * Validates that a password is valid for the given user.
	 *
	 * @param mixed              $evaluation The password evaluation returned from {@see Password_Requirement::evaluate()}.
	 * @param \WP_User|\stdClass $user       The user object. May be an \stdClass
	 *                                       if a user is being edited or created.
	 * @param array              $settings   The selected settings.
	 * @param array              $args       Additional arguments describing the validation.
	 *
	 * @return bool|string Whether the password is valid for this user.
	 *                     Optionally return a message to display to the user in case it is invalid.
	 */
	public function validate( $evaluation, $user, array $settings, array $args );

	/**
	 * Gets the reason a user must change their password.
	 *
	 * @param mixed $evaluation The password evaluation returned from {@see Password_Requirement::evaluate()}.
	 * @param array $settings   The selected settings.
	 *
	 * @return string
	 */
	public function get_reason_message( $evaluation, array $settings ): string;

	/**
	 * Gets the meta key that should be used to store the password's evaluation.
	 *
	 * @return string
	 */
	public function get_meta_key(): string;

	/**
	 * Is the password requirement always enabled, or can it be disabled.
	 *
	 * @return bool
	 */
	public function is_always_enabled(): bool;

	/**
	 * Should passwords be evaluated even if the Password Requirement isn't enabled.
	 *
	 * @return bool
	 */
	public function should_evaluate_if_not_enabled(): bool;

	/**
	 * Gets the settings schema.
	 *
	 * @return array
	 */
	public function get_settings_schema(): array;

	/**
	 * Checks if this password requirement should have an associated user group.
	 *
	 * @return bool
	 */
	public function has_user_group(): bool;
}
