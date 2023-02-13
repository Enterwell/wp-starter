<?php

class ITSEC_Settings_Page_Sidebar_Widget_Sync_Cross_Promo extends ITSEC_Settings_Page_Sidebar_Widget {
	public function __construct() {
		$this->id = 'sync-cross-promo';
		$this->title = __( 'Manage Your Sites Remotely', 'better-wp-security' );
		$this->priority = 11;

		parent::__construct();
	}

	public function render( $form ) {
		?>
		<div style="text-align: center;">
			<img src="<?php echo plugins_url( 'img/sync-logo.png', __FILE__ ) ?>" style="max-width: 100%" alt="Manage Your Sites Remotely">
		</div>
		<?php

		echo '<p>' . __( 'Manage updates (and much more!) for your WordPress websites all in one place. Save time logging in to multiple websites to perform WordPress admin tasks.', 'better-wp-security' ) . '</p>';
		echo '<p>' . __( 'Integrated with iThemes Security, so you can release lockouts, authorize IPs, and turn Away Mode on or off right from your Sync dashboard.', 'better-wp-security' ) . '</p>';
		echo '<div style="text-align: center;">';
		echo '<p><a class="button-primary" href="https://ithemes.com/member/cart.php?action=add&id=523" target="_blank" rel="noopener noreferrer">' . __( 'Free 30 Day Trial', 'better-wp-security' ) . '</a></p>';
		echo '</div>';
	}

}
new ITSEC_Settings_Page_Sidebar_Widget_Sync_Cross_Promo();
