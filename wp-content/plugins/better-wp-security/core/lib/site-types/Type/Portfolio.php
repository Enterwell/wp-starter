<?php

namespace iThemesSecurity\Lib\Site_Types\Type;

use iThemesSecurity\Lib\Site_Types\Question\Client_Question_Pack;
use iThemesSecurity\Lib\Site_Types\Question\Global_Question_Pack;
use iThemesSecurity\Lib\Site_Types\Question\Login_Security_Question_Pack;
use iThemesSecurity\Lib\Site_Types\Question\Site_Scan_Question;
use iThemesSecurity\Lib\Site_Types\Site_Type;

final class Portfolio implements Site_Type {
	public function get_slug(): string {
		return self::PORTFOLIO;
	}

	public function get_title(): string {
		return __( 'Portfolio', 'better-wp-security' );
	}

	public function get_description(): string {
		return __( 'A website to showcase your craft.', 'better-wp-security' );
	}

	public function get_icon(): string {
		return 'format-gallery';
	}

	public function get_questions(): array {
		return array_merge(
			[ new Site_Scan_Question() ],
			( new Login_Security_Question_Pack() )->get_questions(),
			( new Client_Question_Pack() )->get_questions(),
			( new Global_Question_Pack() )->get_questions(),
		);
	}
}
