<?php

namespace iThemesSecurity\Lib\Site_Types;

interface Question {

	const IS_CLIENT = 'is-client';
	const SELECT_CLIENTS = 'select-clients';
	const SELECT_SITE_ADMINS = 'select-site-admins';
	const CLIENTS_CAN_MANAGE = 'clients-can-manage';
	const SELECT_END_USERS = 'select-end-users';
	const END_USERS_TWO_FACTOR = 'end-users-two-factor';
	const END_USERS_PASSWORD_POLICY = 'end-users-password-policy';
	const SECURITY_CHECK_PRO = 'security-check-pro';

	/**
	 * Gets a unique id identifying this question.
	 *
	 * @return string
	 */
	public function get_id(): string;

	/**
	 * Gets the question prompt.
	 *
	 * @return string
	 */
	public function get_prompt(): string;

	/**
	 * Gets a longer description for the question.
	 *
	 * @return string
	 */
	public function get_description(): string;

	/**
	 * Gets the schema used to validate the user's answer.
	 *
	 * @return array
	 */
	public function get_answer_schema(): array;
}
