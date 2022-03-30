<?php

use EwStarter\Configuration\Ew_Twig;

if ( ! function_exists( 'ew_render_template' ) ):

	/**
	 * Wrapper to render function of Ew_Twig, renders template
	 * with given data.
	 *
	 * @var string $template
	 * @var array $template_data
	 * @throws Exception
	 */
	function ew_render_template( string $template, array $template_data ): void {
		Ew_Twig::get_instance()->render(
			$template,
			$template_data
		);
	}
endif;
