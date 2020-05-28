<?php

class ITSEC_Admin_Notice_New_Feature_Core implements ITSEC_Admin_Notice {

	public function get_id() {
		return 'release-user-groups';
	}

	public function get_title() {
		return '';
	}

	public function get_message() {
		return sprintf(
			esc_html__( 'New in iThemes Security version %1$s, %2$sSave Time Securing WordPress With User Groups%3$s!', 'better-wp-security' ),
			ITSEC_Core::is_pro() ? '6.4.0' : '7.7.0',
			'<a href="{{ $blog }}">',
			'</a>'
		);
	}

	public function get_meta() {
		return array();
	}

	public function get_severity() {
		return self::S_INFO;
	}

	public function show_for_context( ITSEC_Admin_Notice_Context $context ) {
		return true;
	}

	public function get_actions() {
		return array(
			'blog' => new ITSEC_Admin_Notice_Action_Link(
				add_query_arg( 'itsec_view_release_post', 'user-groups', admin_url( 'index.php' ) ),
				esc_html__( 'See How It Works', 'better-wp-security' ),
				ITSEC_Admin_Notice_Action::S_PRIMARY,
				function () {
					$this->handle_dismiss();

					wp_redirect( 'https://ithemes.com/new-save-time-securing-wordpress-with-user-groups' );
					die;
				}
			)
		);
	}

	private function handle_dismiss() {
		$dismissed   = $this->get_storage();
		$dismissed[] = $this->get_id();
		$this->save_storage( $dismissed );

		return null;
	}

	private function get_storage() {
		$dismissed = get_site_option( 'itsec_dismissed_notices', array() );

		if ( ! is_array( $dismissed ) ) {
			$dismissed = array();
		}

		return $dismissed;
	}

	private function save_storage( $storage ) {
		update_site_option( 'itsec_dismissed_notices', $storage );
	}
}

ITSEC_Lib_Admin_Notices::register( new ITSEC_Admin_Notice_Globally_Dismissible( new ITSEC_Admin_Notice_Managers_Only( new ITSEC_Admin_Notice_New_Feature_Core() ) ) );
