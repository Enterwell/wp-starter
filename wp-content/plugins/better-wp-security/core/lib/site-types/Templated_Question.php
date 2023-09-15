<?php

namespace iThemesSecurity\Lib\Site_Types;

abstract class Templated_Question implements Question {

	/** @var Templating_Site_Type */
	protected $site_type;

	/**
	 * Templated_Question constructor.
	 *
	 * @param Templating_Site_Type $site_type
	 */
	public function __construct( Templating_Site_Type $site_type ) { $this->site_type = $site_type; }

	public function get_prompt(): string {
		if ( $this->site_type->is_supported_question( $this->get_id() ) ) {
			return $this->site_type->make_prompt( $this->get_id() );
		}

		return $this->get_prompt_fallback();
	}

	public function get_description(): string {
		$description = $this->get_description_fallback();

		if ( $description && $this->site_type->is_supported_question( $this->get_id() ) ) {
			$description = $this->site_type->make_description( $this->get_id() ) ?: $description;
		}

		return $description;
	}

	abstract protected function get_prompt_fallback(): string;

	protected function get_description_fallback(): string { return ''; }
}
