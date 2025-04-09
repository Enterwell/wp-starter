<?php

namespace iThemesSecurity\Contracts;

use iThemesSecurity\Import_Export\Export\Export;
use iThemesSecurity\Import_Export\Import\Import_Context;
use iThemesSecurity\Import_Export\Import\Transformation;
use iThemesSecurity\Lib\Result;

interface Import_Export_Source {

	public function get_export_slug(): string;

	public function get_export_title(): string;

	public function get_export_description(): string;

	public function get_export_options_schema(): array;

	public function get_export_schema(): array;

	/**
	 * Gets the list of transformations.
	 *
	 * @return Transformation[]
	 */
	public function get_transformations(): array;

	public function export( $options ): Result;

	public function import( Export $from, Import_Context $context ): Result;
}
