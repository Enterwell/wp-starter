<?php

namespace iThemesSecurity\Lib\Site_Types;

interface Templating_Site_Type extends Site_Type {
	public function is_supported_question( string $question_id ): bool;

	public function make_prompt( string $question_id ): string;

	public function make_description( string $question_id ): string;
}
