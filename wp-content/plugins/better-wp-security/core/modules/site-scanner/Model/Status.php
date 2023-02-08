<?php

namespace iThemesSecurity\Site_Scanner;

interface Status {
	const CLEAN = 'clean';
	const WARN = 'warn';
	const ERROR = 'error';

	const ALL = [ self::CLEAN, self::WARN, self::ERROR ];
}
