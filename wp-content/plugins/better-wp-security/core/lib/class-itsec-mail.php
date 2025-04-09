<?php

final class ITSEC_Mail {
	private $name;
	private $content = '';
	private $groups = array();
	private $current_group;
	private $deferred = '';
	private $subject = '';
	private $recipients = array();
	private $attachments = array();
	private $template_path = '';

	public function __construct( $name = '' ) {
		$this->template_path = dirname( __FILE__ ) . '/mail-templates/';
		$this->name          = $name;
	}

	public function add_header( $title, $banner_title, $use_site_logo = false, $sub_title = '' ) {
		if ( strlen( $sub_title ) ) {
			$header = $this->get_template( 'header-subtitle.html' );
		} else {
			$header = $this->get_template( 'header.html' );
		}

		if ( $use_site_logo ) {
			$logo = $this->get_site_logo_url();

			if ( ! $logo ) {
				if ( ITSEC_Core::is_pro() ) {
					$logo = $this->get_image_url( 'solid_security_pro_logo' );
				} else {
					$logo = $this->get_image_url( 'solid_security_logo' );
				}
			}
		} else {
			if ( ITSEC_Core::is_pro() ) {
				$logo = $this->get_image_url( 'solid_security_pro_logo' );
			} else {
				$logo = $this->get_image_url( 'solid_security_logo' );
			}
		}

		$replacements = array(
			'lang'         => esc_attr( get_bloginfo( 'language' ) ),
			'charset'      => esc_attr( get_bloginfo( 'charset' ) ),
			'title_tag'    => $title,
			'banner_title' => $banner_title,
			'logo'         => $logo,
			'title'        => $title,
			'sub_title'    => $sub_title
		);

		$this->add_html( $this->replace_all( $header, $replacements ), 'header' );
	}

	public function add_user_header( $title, $banner_title ) {
		$header  = $this->get_template( 'header-user.html' );
		$logoUrl = $this->get_site_logo_url();
		$logo    = '';

		if ( $logoUrl ) {
			$logo = "<img src='{$logoUrl}' alt='Logo' style='display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;font-size:12px; max-width:312px; width:100%;' title='Logo'>";
		}

		$replacements = array(
			'lang'         => esc_attr( get_bloginfo( 'language' ) ),
			'charset'      => esc_attr( get_bloginfo( 'charset' ) ),
			'title_tag'    => $title,
			'banner_title' => $banner_title,
			'title'        => $title,
			'logo'         => $logo,
		);

		$this->add_html( $this->replace_all( $header, $replacements ), 'header' );
	}

	public function add_footer( $add_divider = true ) {
		$settings_link    = esc_url( self::filter_admin_page_url( ITSEC_Core::get_settings_page_url() ) );
		$security_link    = ITSEC_Core::get_tracking_link(
			'https://solidwp.com/security/',
			'email',
			'link'
		);
		$articles_link    = ITSEC_Core::get_tracking_link(
			'https://go.solidwp.com/security-blog-category',
			'email_footer',
			'link'
		);
		$tutorials_link   = ITSEC_Core::get_tracking_link(
			'https://go.solidwp.com/security-footer-tutorials-category',
			'email_footer',
			'link'
		);
		$vuln_report_link = ITSEC_Core::get_tracking_link(
			'https://go.solidwp.com/footer-vuln-report',
			'email_footer',
			'link'
		);
		$support_link     = ITSEC_Core::get_tracking_link(
			'https://go.solidwp.com/footer-helpdesk',
			'email_footer',
			'link'
		);
		$sec_guide_link   = ITSEC_Core::get_tracking_link(
			'https://go.solidwp.com/footer-security-pocket-guide',
			'email_footer',
			'link'
		);

		$footer = '';

		if ( ! ITSEC_Core::is_pro() ) {
			$callout = $this->get_template( 'pro-callout.html' );

			$replacements = array(
				'two_factor'       => esc_html__( 'Want two-factor authentication, scheduled site scanning, ticketed support and more?', 'better-wp-security' ),
				'get_pro'          => esc_html__( 'Get Solid Security Pro', 'better-wp-security' ),
				'go_pro_link'      => $security_link,
				'why_pro'          => esc_html__( 'Why Go Pro', 'better-wp-security' ),
				'why_go_pro_image' => $this->get_image_url( 'go-pro-shield' ),
			);

			$footer .= $this->replace_all( $callout, $replacements );
		} elseif ( $add_divider ) {
			$this->add_divider();
		}

		$footer .= $this->get_template( 'footer.html' );

		$replacements = array(
			'security_resources'     => esc_html__( 'More Website Security Resources', 'better-wp-security' ),
			'articles'               => esc_html__( 'WordPress Security News', 'better-wp-security' ),
			'articles_link'          => $articles_link,
			'articles_content'       =>
				sprintf(
					esc_html__(
						'Be the first to get the latest WordPress security news, tips, and updates on the %1$sSolidWP Blog%3$s, including the %2$sWeekly WordPress Vulnerability Report.%3$s', 'better-wp-security'
					),
					"<a style=\"color: #3C1596; text-decoration: underline;\" href=\"{$articles_link}\">",
					"<a style=\"color: #3C1596; text-decoration: underline;\" href=\"{$vuln_report_link}\">",
					'</a>',
				),
			'tutorials'              => esc_html__( 'Tutorials', 'better-wp-security' ),
			'tutorials_content'      => esc_html__( 'Vulnerable WordPress plugins and themes are the #1 reason WordPress sites get hacked. Either quickly update the vulnerable theme, plugin or WordPress version immediately to the newest version or immediately deactivate the plugin or theme until a fix is available.', 'better-wp-security' ),
			'help_and_support'       => esc_html__( 'Help & Support', 'better-wp-security' ),
			'documentation'          => esc_html__( 'Documentation', 'better-wp-security' ),
			'documentation_content'  =>
				sprintf(
					esc_html__(
						'Read Solid Security documentation and Frequently Asked Questions on the %1$sSolidWP Help Center.%2$s', 'better-wp-security'
					), "<a style=\"color: #3C1596; text-decoration: underline;\" href=\"{$tutorials_link}\">", '</a>',
				),
			'support_link'           => $support_link,
			'support_content'        =>
				sprintf(
					esc_html__(
						'Pro customers have the best support team available as their security team. Contact the %1$s SolidWP Help Desk%2$s for help when you need answers.', 'better-wp-security'
					), "<a style=\"color: #3C1596; text-decoration: underline;\" href=\"{$support_link}\">", '</a>'
				),
			'security_settings_link' => $settings_link,
			'unsubscribe_link_text'  =>
				esc_html__( 'This email was generated by the Solid Security plugin.', 'better-wp-security' ) . '<br>' .
				sprintf(
					esc_html__(
						'To unsubscribe from these updates, visit the %1$sSettings page%2$s in the Solid Security plugin menu.', 'better-wp-security'
					), "<a href=\"{$settings_link}\" style=\"color: #32174D; text-decoration: underline;\">", '</a>'
				),
			'support'                => esc_html__( 'Support', 'better-wp-security' ),
			'pro'                    => esc_html__( 'Pro Feature', 'better-wp-security' ),
			'security_guide'         => esc_html__( 'Check Out Our Free WordPress Security Guide', 'better-wp-security' ),
			'security_guide_content' => sprintf(
				esc_html__(
					'Learn simple WordPress security tips — including 3 kinds of security your site needs and 4 best security practices for keeping your WordPress site safe with our %1$sfree guide.%2$s', 'better-wp-security'
				), "<a style=\"color: #3C1596; text-decoration: underline;\" href=\"{$sec_guide_link}\">", '</a>'
			),
		);

		$this->add_html( $this->replace_all( $footer, $replacements ) );

		if ( defined( 'ITSEC_DEBUG' ) && ITSEC_DEBUG ) {
			$this->include_debug_info();
		}

		$this->add_html( $this->get_template( 'close.html' ), 'footer' );
	}

	public function add_user_footer() {

		$link_text = sprintf( esc_html__( 'This email was generated by the Solid Security plugin on behalf of %s.', 'better-wp-security' ), get_bloginfo( 'name', 'display' ) ) . '<br>';
		$link_text .= sprintf(
			esc_html__( 'To unsubscribe from these notifications, please %1$scontact the site administrator%2$s.', 'better-wp-security' ),
			'<a href="' . esc_url( site_url() ) . '" style="color: #3C1596; text-decoration: underline;">', '</a>'
		);

		$footer = $this->replace_all( $this->replace_images( $this->get_template( 'footer-user.html' ) ), array(
			'unsubscribe_link_text' => $link_text,
		) );

		$footer .= $this->get_template( 'close.html' );
		$this->add_html( $footer, 'user-footer' );
	}

	public function add_text( $content, $color = 'light' ) {
		$this->add_html( $this->get_text( $content, $color ) );
	}

	public function get_text( $content, $color = 'light' ) {
		$module = $this->get_template( 'text.html' );
		$module = $this->replace( $module, 'content', $content );
		$module = $this->replace( $module, 'color', $color === 'dark' ? '#002338' : '#808080' );

		return $module;
	}

	public function add_divider() {
		$this->add_html( $this->get_divider() );
	}

	public function get_divider() {
		return $this->get_template( 'divider.html' );
	}

	public function add_large_text( $content ) {
		$this->add_html( $this->get_large_text( $content ) );
	}

	public function get_large_text( $content ) {
		$module = $this->get_template( 'large-text.html' );
		$module = $this->replace( $module, 'content', $content );

		return $module;
	}

	public function add_info_box( $content, $icon_type = 'info' ) {
		$this->add_html( $this->get_info_box( $content, $icon_type ) );
	}

	public function get_info_box( $content, $icon_type = 'info' ) {
		$icon_url = $this->get_image_url( $icon_type === 'warning' ? 'warning_icon_yellow' : "{$icon_type}_icon" );

		$module = $this->get_template( 'info-box.html' );
		$module = $this->replace_all( $module, compact( 'content', 'icon_url' ) );

		return $module;
	}

	public function add_details_box( $content ) {
		$this->add_html( $this->get_details_box( $content ) );
	}

	public function get_details_box( $content ) {
		$module = $this->get_template( 'details-box.html' );
		$module = $this->replace( $module, 'content', $content );

		return $module;
	}

	public function add_123_box( $first, $second, $third ) {
		$this->add_html( $this->get_123_box( $first, $second, $third ) );
	}

	public function get_123_box( $first, $second, $third ) {
		$module = $this->get_template( '123-box.html' );
		$module = $this->replace( $module, 'first', $first );
		$module = $this->replace( $module, 'second', $second );
		$module = $this->replace( $module, 'third', $third );

		return $module;
	}

	/**
	 * @deprecated 8.0.0
	 *
	 * @param $content
	 *
	 * @return void
	 */
	public function add_large_code( $content ) {
		$this->add_html( $this->get_large_code( $content ) );
	}

	public function get_large_code( $content ) {
		$module = $this->get_template( 'large-code.html' );
		$module = $this->replace( $module, 'content', $content );

		return $module;
	}

	public function add_small_code( $content ) {
		$this->add_html( $this->get_small_code( $content ) );
	}

	public function get_small_code( $content ) {
		$module = $this->get_template( 'small-code.html' );
		$module = $this->replace( $module, 'content', $content );

		return $module;
	}

	public function add_section_heading( $content, $icon_type = false ) {
		$this->add_html( $this->get_section_heading( $content, $icon_type ) );
	}

	public function get_section_heading( $content, $icon_type = false ) {
		$heading = $this->get_template( 'section-heading.html' );
		$heading = $this->replace_all( $heading, compact( 'content' ) );

		return $heading;
	}

	public function add_lockouts_summary( $user_count, $host_count ) {
		$lockouts = $this->get_template( 'lockouts-summary.html' );

		$replacements = array(
			'users_text' => esc_html__( 'Users', 'better-wp-security' ),
			'hosts_text' => esc_html__( 'Hosts', 'better-wp-security' ),
			'user_count' => $user_count,
			'host_count' => $host_count,
		);

		$lockouts = $this->replace_all( $lockouts, $replacements );

		$this->add_html( $lockouts, 'lockouts-summary' );
	}

	public function add_file_change_summary( $added, $removed, $modified ) {
		$lockouts = $this->get_template( 'file-change-summary.html' );

		$replacements = array(
			'added_text'     => esc_html_x( 'Added', 'Files added', 'better-wp-security' ),
			'removed_text'   => esc_html_x( 'Removed', 'Files removed', 'better-wp-security' ),
			'modified_text'  => esc_html_x( 'Modified', 'Files modified', 'better-wp-security' ),
			'added_count'    => $added,
			'removed_count'  => $removed,
			'modified_count' => $modified,
		);

		$lockouts = $this->replace_all( $lockouts, $replacements );

		$this->add_html( $lockouts, 'file-change-summary' );
	}

	/**
	 * @param $link_text
	 * @param $href
	 *
	 * @return void
	 */
	public function add_button( $link_text, $href ) {
		$this->add_html( $this->get_button( $link_text, $href ) );
	}

	/**
	 * @param $link_text
	 * @param $href
	 *
	 * @return string
	 */
	public function get_button( $link_text, $href ) {

		$module = $this->get_template( 'button.html' );
		$module = $this->replace_all( $module, array(
			'href'      => $href,
			'link_text' => $link_text,
			'bk_color'  => '#232323',
			'txt_color' => '#FFFFFF',
		) );

		return $module;
	}

	public function add_large_button( $link_text, $href, $style = 'default' ) {
		$this->add_html( $this->get_large_button( $link_text, $href, $style ) );
	}

	/**
	 * Adds a large button
	 *
	 * @param string $link_text Content of link
	 * @param string $href      Actual Link value
	 * @param string $style     Additional Styling beyond baseline
	 *
	 * @deprecated 8.0.0
	 *
	 * @return string
	 */
	public function get_large_button( $link_text, $href, $style = 'default' ) {

		$module = $this->get_template( 'large-button.html' );
		$module = $this->replace_all( $module, array(
			'href'      => $href,
			'link_text' => $link_text,
			'bk_color'  => 'blue' === $style ? '#0085E0' : '#FFCD08',
			'txt_color' => 'blue' === $style ? '#FFFFFF' : '#2E280E',
		) );

		return $module;
	}

	public function add_lockouts_table( $lockouts ) {
		$entry   = $this->get_template( 'lockouts-entry.html' );
		$entries = '';

		foreach ( $lockouts as $lockout ) {
			if ( 'user' === $lockout['type'] ) {
				/* translators: 1: Username */
				$lockout['description'] = sprintf( wp_kses( __( '<b>User:</b> %1$s', 'better-wp-security' ), array( 'b' => array() ) ), $lockout['id'] );
			} elseif ( 'username' === $lockout['type'] ) {
				/* translators: 1: Username */
				$lockout['description'] = sprintf( wp_kses( __( '<b>Username:</b> %1$s', 'better-wp-security' ), array( 'b' => array() ) ), $lockout['id'] );
			} else {
				/* translators: 1: IP address */
				$lockout['description'] = sprintf( wp_kses( __( '<b>IP:</b> %1$s', 'better-wp-security' ), array( 'b' => array() ) ), $lockout['id'] );
			}

			$entries .= $this->replace_all( $entry, $lockout );
		}

		$table = $this->get_template( 'lockouts-table.html' );

		$replacements = array(
			'heading_types'  => __( 'IP/User', 'better-wp-security' ),
			'heading_until'  => __( 'Locked Out Until', 'better-wp-security' ),
			'heading_reason' => __( 'Reason', 'better-wp-security' ),
			'entries'        => $entries,
		);

		$table = $this->replace_all( $table, $replacements );

		$this->add_html( $table, 'lockouts-table' );
	}

	/**
	 * Add a generic table.
	 *
	 * @param string[] $headers
	 * @param array[]  $entries
	 * @param bool     $large
	 */
	public function add_table( $headers, $entries, $large = false ) {
		$this->add_html( $this->get_table( $headers, $entries, $large ) );
	}

	public function get_table( $headers, $entries, $large = false ) {

		$template = $this->get_template( 'table.html' );
		$html     = $this->build_table_header( $headers, $large );

		foreach ( $entries as $entry ) {
			$html .= $this->build_table_row( $entry, count( $headers ), $large );
		}

		return $this->replace( $template, 'html', $html );
	}

	/**
	 * Build the table header.
	 *
	 * @param array $headers
	 * @param bool  $large
	 *
	 * @return string
	 */
	private function build_table_header( $headers, $large = false ) {

		$html = '<tr>';

		foreach ( $headers as $header ) {
			$style = 'text-align: left;font-weight: bold;border:1px solid #cdcece;color: #666f72;';

			if ( $large ) {
				$style .= 'padding:15px 20px;font-size: 16px;';
			} else {
				$style .= 'padding:5px 10px;';
			}

			$html .= '<th style="' . $style . '">';
			$html .= $header;
			$html .= '</th>';
		}

		$html .= '</tr>';

		return $html;
	}

	/**
	 * Build a table row.
	 *
	 * @param array|string $columns
	 * @param int          $count
	 * @param bool         $large
	 *
	 * @return string
	 */
	private function build_table_row( $columns, $count, $large = false ) {
		$html = '<tr>';

		if ( is_array( $columns ) ) {
			foreach ( $columns as $i => $column ) {
				$style = 'border:1px solid #cdcece;';

				if ( 0 === $i ) {
					$style .= 'font-style:italic;';
					$el    = 'th';
				} else {
					$el = 'td';
				}

				if ( $large ) {
					$style .= 'padding: 15px 20px;';
				} else {
					$style .= 'padding:10px;';
				}

				$html .= "<{$el} style=\"{$style}\">";
				$html .= $column;
				$html .= "</{$el}>";
			}
		} else {
			$html .= "<td style=\"border:1px solid #cdcece;padding:10px;\" colspan=\"{$count}\">{$columns}</td>";
		}

		$html .= '</tr>';

		return $html;
	}

	public function add_file_change_table( $entries ) {
		$this->add_html( $this->build_file_change_table( $entries ) );
	}

	private function build_file_change_table( $entries ) {
		$template = $this->get_template( 'file-change-table.html' );
		$html     = '';

		foreach ( $entries as $entry ) {
			$html .= $this->build_file_change_row( $entry );
		}

		return $this->replace( $template, 'table_rows', $html );
	}

	private function build_file_change_row( $entry ) {
		$table = $this->get_template( 'file-change-table-row.html' );

		$replacements = array(
			'entry_url'  => $entry[0],
			'entry_date' => $entry[1],
			'entry_hash' => $entry[2],
		);

		return $this->replace_all( $table, $replacements );
	}

	/**
	 * @param $items
	 * @param $header
	 *
	 * @return void
	 */
	public function add_inactive_user_table( $items, $header = 'Username' ) {
		foreach ( $items as $item ) {
			$style = "Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;line-height:21px;color:#333333;font-size:14px;padding:10px";

			$html .= '<p style="' . $style . '">';
			$html .= $item[0];
			$html .= '</p>';
			$html .= '<p style="' . $style . '">';
			$html .= '<strong>Role: ' . $item[1] . '</strong>';
			$html .= '</p>';
			$html .= '<p style="' . $style . '">';
			$html .= '<strong>Last Active: ' . $item[2] . '</strong>';
			$html .= '</p>';
		}

		return $html;

	}

	/**
	 * Add an HTML list to an email.
	 *
	 * @param string[] $items
	 * @param bool     $bold_first     Whether to emphasize the first item of the list.
	 * @param bool     $use_paragraphs Whether to use p tags instead of ul
	 * @param string   $title          The entry title
	 */
	public function add_list( $items, $bold_first = false, $use_paragraphs = false, $title = '' ) {
		if ( $use_paragraphs ) {
			$this->add_html( $this->get_paragraph_list( $items, $title ) );
		} else {
			$this->add_section_heading( $title );
			$this->add_html( $this->get_list( $items, $bold_first ) );
		}

	}

	/**
	 * @param string[] $items      Items to add to the list
	 * @param bool     $bold_first Whether to emphasize the first item of the list
	 *
	 * @return string
	 */
	public function get_list( $items, $bold_first = false ) {

		$template = $this->get_template( 'list.html' );
		$html     = '';

		foreach ( $items as $i => $item ) {
			$html .= $this->build_list_item( $item, $bold_first && 0 === $i );
		}

		return $this->replace( $template, 'html', $html );
	}

	/**
	 * @param $items
	 * @param $title
	 *
	 * @return string
	 */
	public function get_paragraph_list( $items, $title ) {
		$template   = $this->get_template( 'list-paragraphs.html' );
		$paragraphs = '';

		foreach ( $items as $i => $item ) {
			$paragraphs .= $this->build_list_item_paragraphs( $item );
		}

		$replacements = [
			'content_title' => $title,
			'content'       => $paragraphs,
		];

		return $this->replace_all( $template, $replacements );
	}


	private function build_list_item( $item, $bold = false ) {
		$bold_tag = $bold ? 'font-weight: bold;' : '';

		return "<li style=\"margin: 0; padding: 5px 10px;{$bold_tag}\">{$item}</li>";
	}

	private function build_list_item_paragraphs( $item ): string {
		return "<p style=\"Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:helvetica, 'helvetica neue', arial, verdana, sans-serif;line-height:23px;font-size:15px\">{$item}</p>";
	}

	/**
	 * Add an image to the email.
	 *
	 * @param string $src_or_name URL of the image or the name of the mail image.
	 * @param int    $width       Max width of the image in pixels.
	 */
	public function add_image( $src_or_name, $width ) {
		$this->add_html( $this->get_image( $src_or_name, $width ) );
	}

	public function get_image( $src_or_name, $width ) {
		if ( false === strpos( $src_or_name, '.' ) ) {
			$src = $this->get_image_url( $src_or_name );
		} else {
			$src = $src_or_name;
		}

		$module = $this->get_template( 'image.html' );
		$module = $this->replace_all( $module, array(
			'src'   => $src,
			'width' => $width,
		) );

		return $module;
	}

	public function add_site_scanner_pro_callout() {
		$this->add_html( $this->get_site_scanner_pro_callout() );
	}

	public function get_site_scanner_pro_callout() {
		$template = $this->get_template( 'site-scanner-pro-callout.html' );
		$template = $this->replace_all( $template, array(
			'title'             => esc_html__( 'Go Pro Now to Get Automatic Vulnerability Patching', 'better-wp-security' ),
			'content'           =>
				wp_kses(
					__( 'Solid Security Pro will <b>automatically update vulnerable plugins and themes for you</b> if a patch is available <b>so you don’t have to manually log in to update</b>.', 'better-wp-security' ),
					'mail'
				) . ' ' .
				esc_html__( 'Get your site patched by Solid Security Pro before hackers discover vulnerabilities on your site, all without doing a thing.', 'better-wp-security' ) . '<br><br>' .
				sprintf(
					'<b>%s</b>',
					sprintf(
						esc_html__( 'Get all of this in the %1$sSite Scanner Pro%2$s:', 'better-wp-security' ),
						'<a href="' . ITSEC_Core::get_tracking_link( 'https://go.solidwp.com/pro-feature-site-scan', 'sitescanemail', 'link' ) . '">',
						'</a>'
					)
				),
			'first_box_header'  => esc_html__( 'Save Time', 'better-wp-security' ),
			'first_box'         => esc_html__( 'Save time when time is of the utmost importance to patch vulnerabilities before hackers and bots can find and exploit them.', 'better-wp-security' ),
			'second_box_header' => esc_html__( 'More Freedom', 'better-wp-security' ),
			'second_box'        => esc_html__( 'Free your team from mundane updates by removing the need to manually log in to update plugins and themes.', 'better-wp-security' ),
			'third_box_header'  => esc_html__( 'Automatic Updates', 'better-wp-security' ),
			'third_box'         => esc_html__( 'Automatically updates vulnerable plugins, themes, and WordPress core for you if it fixes a vulnerability that was found by the Site Scanner.', 'better-wp-security' ),
			'fourth_box_header' => esc_html__( 'Increase Security', 'better-wp-security' ),
			'fourth_box'        => esc_html__( 'Hardens your website if you are running outdated software, including checks for old WordPress sites that could be used to compromise your server.', 'better-wp-security' ),
			'href'              => ITSEC_Core::get_tracking_link( 'https://go.solidwp.com/footer-site-scan', 'sitescanemail', 'button' ),
			'link_text'         => __( 'Go Pro Now', 'better-wp-security' ),
			'bk_color'          => '#772ecb',
			'txt_color'         => '#FFFFFF',
		) );

		return $template;
	}

	/**
	 * Add a section of HTML to the email.
	 *
	 * @param string      $html
	 * @param string|null $identifier
	 */
	public function add_html( $html, $identifier = null ) {

		if ( null !== $this->current_group ) {
			$this->deferred .= $html;
		} elseif ( null !== $identifier ) {
			$this->groups[ $identifier ] = $html;
		} else {
			$this->groups[] = $html;
		}
	}

	public function start_group( $identifier ) {
		$this->current_group = $identifier;
	}

	public function end_group() {
		$group    = $this->current_group;
		$deferred = $this->deferred;

		$this->current_group = null;
		$this->deferred      = '';

		$this->add_html( $deferred, $group );
	}

	public function insert_before( $identifier, $html ) {
		$this->groups = ITSEC_Lib::array_insert_before( $identifier, $this->groups, count( $this->groups ), $html );
	}

	public function insert_after( $identifier, $html ) {
		$this->groups = ITSEC_Lib::array_insert_after( $identifier, $this->groups, count( $this->groups ), $html );
	}

	/**
	 * Include debug info in the email.
	 *
	 * This is automatically included in non-user emails if ITSEC_DEBUG is turned on.
	 */
	public function include_debug_info() {

		if ( ( defined( 'DOING_CRON' ) && DOING_CRON ) || ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) ) {
			$page = 'WP-Cron';
		} elseif ( defined( 'WP_CLI' ) && WP_CLI ) {
			$page = 'WP-CLI';
		} elseif ( isset( $_SERVER['HTTP_HOST'], $_SERVER['REQUEST_URI'] ) ) {
			$page = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		} else {
			$page = __( 'unknown', 'better-wp-security' );
		}

		$this->add_text( sprintf( esc_html__( 'Debug info (source page): %s', 'better-wp-security' ), esc_html( $page ) ) );
	}

	/**
	 * Get the site URL formatted for display in emails.
	 *
	 * This strips out the URL scheme, but keeps the path in case of multisite.
	 *
	 * @return string
	 */
	public function get_display_url() {

		$url    = network_site_url();
		$parsed = parse_url( $url );

		$display = $parsed['host'];

		if ( ! empty( $parsed['path'] ) ) {
			$display .= $parsed['path'];
		}

		// Escape URL will force a scheme.
		return esc_html( $display );
	}

	public function set_content( $content ) {
		$this->content = $content;
	}

	public function get_content( $recipient = '' ) {

		$groups = $this->groups;

		if ( $this->name ) {
			/**
			 * Filter the HTML groups before building the content.
			 *
			 * @param array      $groups
			 * @param ITSEC_Mail $this
			 * @param string     $recipient
			 */
			$groups = apply_filters( "itsec_mail_{$this->name}", $groups, $this, $recipient );
		}

		return implode( '', $groups );
	}

	public function set_subject( $subject, $add_site_url = true ) {
		if ( $add_site_url ) {
			$subject = $this->prepend_site_url_to_subject( $subject );
		}

		$this->subject = esc_html( $subject );
	}

	public function prepend_site_url_to_subject( $subject ) {
		/* translators: 1: site URL, 2: email subject */
		return sprintf( __( '[%1$s] %2$s', 'better-wp-security' ), $this->get_display_url(), $subject );
	}

	public function set_default_subject() {
		return __( 'New Notification from Solid Security', 'better-wp-security' );
	}

	public function get_subject() {
		return $this->subject;
	}

	public function set_recipients( $recipients ) {
		$this->recipients = array();

		foreach ( (array) $recipients as $recipient ) {
			$recipient = trim( $recipient );

			if ( is_email( $recipient ) ) {
				$this->recipients[] = $recipient;
			}
		}
	}

	public function set_default_recipients() {
		$recipients = ITSEC_Modules::get_setting( 'global', 'notification_email' );
		$this->set_recipients( $recipients );
	}

	public function get_recipients() {
		return $this->recipients;
	}

	public function set_attachments( $attachments ) {
		$this->attachments = $attachments;
	}

	public function add_attachment( $attachment ) {
		$this->attachments[] = $attachment;
	}

	public function send() {
		if ( empty( $this->recipients ) ) {
			$this->set_default_recipients();
		}

		if ( empty( $this->subject ) ) {
			$this->set_default_subject();
		}

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
		);

		if ( $from = ITSEC_Modules::get_setting( 'notification-center', 'from_email' ) ) {
			$headers[] = "From: <{$from}>";
		}

		if ( $this->name ) {
			$result = true;

			foreach ( $this->recipients as $recipient ) {
				$result = wp_mail( $recipient, $this->get_subject(), $this->content ?: $this->get_content( $recipient ), $headers, $this->attachments ) && $result;
			}

			return $result;
		}

		return wp_mail( $this->recipients, $this->get_subject(), $this->content ?: $this->get_content(), $headers, $this->attachments );
	}

	/**
	 * Get the URL to the site logo.
	 *
	 * @return string
	 */
	private function get_site_logo_url() {
		$size           = array( 300, 127 );
		$custom_logo_id = get_theme_mod( 'custom_logo' );

		if ( ! $custom_logo_id ) {
			$url = '';
		} else {
			$image = wp_get_attachment_image_src( $custom_logo_id, $size );

			if ( ! $image || empty( $image[0] ) ) {
				$url = '';
			} else {
				$url = $image[0];
			}
		}

		/**
		 * Filters the URL to the Site Logo for use in email notifications.
		 *
		 * @param string $url
		 * @param array  $size
		 */
		return apply_filters( 'solid_security_mail_site_logo', $url, $size );
	}

	private function get_template( $template ) {
		return $this->replace_images( file_get_contents( $this->template_path . $template ) );
	}

	private function replace( $content, $variable, $value ) {
		return ITSEC_Lib::replace_tag( $content, $variable, $value );
	}

	private function replace_all( $content, $replacements ) {
		return ITSEC_Lib::replace_tags( $content, $replacements );
	}

	private function replace_images( $content ) {
		return preg_replace_callback( '/{! \$([a-zA-Z_][\w]*) }}/', array( $this, 'replace_image_callback' ), $content );
	}

	private function replace_image_callback( $matches ) {
		if ( empty( $matches ) || empty( $matches[1] ) ) {
			return '';
		}

		ITSEC_Log::add_notice( 'mail', 'mail-debugging', $matches );

		if ( $matches[1] === 'footer_logo' ) {
			return esc_url( $this->get_image_url( $matches[1], '.svg' ) );
		}

		return esc_url( $this->get_image_url( $matches[1] ) );

	}

	/**
	 * @param string $name      Name of the image to get
	 * @param string $extension File type extension
	 *
	 * @return string
	 */
	private function get_image_url( $name, $extension = '.png' ) {
		return plugin_dir_url( ITSEC_Core::get_core_dir() . 'img/mail/index.php' ) . $name . $extension;
	}

	public static function filter_admin_page_url( $url ) {

		/**
		 * Filter admin page URLs so modules can add any necessary security tokens.
		 *
		 * @since 6.4.0
		 *
		 * @param string $url
		 */
		return apply_filters( 'itsec_notify_admin_page_url', $url );
	}

	/**
	 * Add a device.
	 *
	 * @param array[] $device
	 * @param bool    $large
	 */
	public function add_device( $device ) {
		$this->add_html( $this->get_device( $device ) );
	}

	/**
	 * Build the html for the pending device
	 *
	 * @param array[] $device
	 *
	 * @return string
	 */
	public function get_device( $device ) {
		$template     = $this->get_template( 'device.html' );
		$column_left  = '';
		$column_right = '';

		foreach ( $device as $entry ) {
			if ( $entry['position'] === 'left' ) {
				$column_left .= $this->build_left_column( $entry );
			} else {
				$column_right .= $this->build_right_column( $entry );
			}
		}
		$template = $this->replace_all( $template, array(
			'column_left'  => $column_left,
			'column_right' => $column_right
		) );

		return $template;
	}

	/**
	 * Builds device rows.
	 *
	 * @param array|string $entry
	 *
	 * @return string
	 */
	private function build_left_column( $entry ) {
		$label_style = 'color: #545454; font-size: 12px; line-height: 16px; margin-block: 0; margin-right: 16px;';
		$value_style = 'color: #232323; font-weight: 600; font-size: 13px; line-height: 16px; margin-block-start: 4px;';
		$left_column = '<tr>';
		$left_column .=
			"<td style='vertical-align: top;'>
				<p style=\"{$label_style}\">{$entry['label']}</p>
				<p style=\"{$value_style}\">{$entry['value']}</p>
			</td>";
		$left_column .= '</tr>';

		return $left_column;
	}

	private function build_right_column( $entry ) {
		$label_style  = 'color: #545454; font-size: 12px; line-height: 16px; margin-block: 0; margin-right: 16px;';
		$value_style  = 'color: #232323; font-weight: 600; font-size: 13px; line-height: 16px; margin-block-start: 4px';
		$right_column = '<tr>';
		$right_column .=
			"<td style='vertical-align: top;'>
				<p style=\"{$label_style}\">{$entry['label']}</p>
				<p style=\"{$value_style}\">{$entry['value']}</p>
			</td>";
		$right_column .= '</tr>';

		return $right_column;
	}

	/**
	 * Add device actions row
	 */
	public function add_device_action_row( $buttons ) {
		$this->add_html( $this->build_device_action_row( $buttons ) );
	}

	/**
	 * Builds device actions row
	 */
	public function build_device_action_row( $buttons ) {
		$template = $this->get_template( 'device-action-row.html' );
		$template = $this->replace_all( $template, array(
			'block_href'           => $buttons['block']['action'],
			'block_text'           => $buttons['block']['text'],
			'block_bk_color'       => '#ffffff',
			'block_border_color'   => '#d63638',
			'block_text_color'     => '#d63638',
			'confirm_href'         => $buttons['confirm']['action'],
			'confirm_text'         => $buttons['confirm']['text'],
			'confirm_bk_color'     => '#ffffff',
			'confirm_border_color' => '#6817c5',
			'confirm_text_color'   => '#6817c5'
		) );

		return $template;
	}
}
