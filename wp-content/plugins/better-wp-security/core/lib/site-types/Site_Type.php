<?php

namespace iThemesSecurity\Lib\Site_Types;

interface Site_Type {
	const ECOMMERCE = 'ecommerce';
	const NETWORK = 'network';
	const NON_PROFIT = 'non-profit';
	const BLOG = 'blog';
	const BROCHURE = 'brochure';
	const PORTFOLIO = 'portfolio';

	/**
	 * Gets the slug.
	 *
	 * @return string
	 */
	public function get_slug(): string;

	/**
	 * Gets the title.
	 *
	 * @return string
	 */
	public function get_title(): string;

	/**
	 * Gets the description.
	 *
	 * @return string
	 */
	public function get_description(): string;

	/**
	 * Gets the icon name.
	 *
	 * @return string
	 */
	public function get_icon(): string;

	/**
	 * Gets the list of all possible Questions this site type asks.
	 *
	 * @return Question[]
	 */
	public function get_questions(): array;
}
