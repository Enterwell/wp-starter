<?php

namespace iThemesSecurity\Lib\Tools;

use iThemesSecurity\Lib\Result;

interface Tool {
	/**
	 * Gets the Tool's slug.
	 *
	 * @return string
	 */
	public function get_slug(): string;

	/**
	 * Gets the Tool's module.
	 *
	 * @return string
	 */
	public function get_module(): string;

	/**
	 * Can this tool be run.
	 *
	 * @return bool
	 */
	public function is_available(): bool;

	/**
	 * Gets the condition definition in which this tool can be run.
	 *
	 * @return array
	 */
	public function get_condition(): array;

	/**
	 * Gets the Tool's title.
	 *
	 * @return string
	 */
	public function get_title(): string;

	/**
	 * Gets the Tool's description.
	 *
	 * @return string
	 */
	public function get_description(): string;

	/**
	 * Gets the Tool's search keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords(): array;

	/**
	 * Gets the Tool's help content.
	 *
	 * @return string
	 */
	public function get_help(): string;

	/**
	 * Is this a toggleable tool.
	 *
	 * @return bool
	 */
	public function is_toggleable(): bool;

	/**
	 * If this is a scheduled tool, returns the schedule id.
	 *
	 * @return string
	 */
	public function get_schedule(): string;

	/**
	 * If this Tool accepts user input, returns the JSON Schema for the form.
	 *
	 * @return array
	 */
	public function get_form(): array;

	/**
	 * Runs the tool.
	 *
	 * @param array $form User provided form data, validated and sanitized according to the Schema.
	 *
	 * @return Result
	 */
	public function run( array $form = [] ): Result;
}
