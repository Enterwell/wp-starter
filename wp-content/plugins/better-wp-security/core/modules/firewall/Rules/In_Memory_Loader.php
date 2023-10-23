<?php

namespace iThemesSecurity\Modules\Firewall\Rules;

class In_Memory_Loader implements Loader {

	private $rules;

	public function __construct( array $rules ) { $this->rules = $rules; }

	public function load_rules(): array {
		return $this->rules;
	}
}
