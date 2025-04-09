<?php

namespace iThemesSecurity\Lib\Site_Types;

final class Templating_Site_Type_Adapter implements Templating_Site_Type {

	/** @var Site_Type */
	private $site_type;

	/**
	 * Templating_Site_Type_Adapter constructor.
	 *
	 * @param Site_Type $site_type
	 */
	public function __construct( Site_Type $site_type ) { $this->site_type = $site_type; }

	public function is_supported_question( string $question_id ): bool {
		return $this->site_type instanceof Templating_Site_Type ? $this->site_type->is_supported_question( $question_id ) : false;
	}

	public function make_prompt( string $question_id ): string {
		return $this->site_type instanceof Templating_Site_Type ? $this->site_type->make_prompt( $question_id ) : '';
	}

	public function make_description( string $question_id ): string {
		return $this->site_type instanceof Templating_Site_Type ? $this->site_type->make_description( $question_id ) : '';
	}

	public function get_slug(): string {
		return $this->site_type->get_slug();
	}

	public function get_title(): string {
		return $this->site_type->get_title();
	}

	public function get_description(): string {
		return $this->site_type->get_description();
	}

	public function get_icon(): string {
		return $this->site_type->get_icon();
	}

	public function get_questions(): array {
		return $this->site_type->get_questions();
	}
}
