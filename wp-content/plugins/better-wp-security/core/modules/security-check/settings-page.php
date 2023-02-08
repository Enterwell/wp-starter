<?php

final class ITSEC_Security_Check_Settings_Page extends ITSEC_Module_Settings_Page {
	private $script_version = 4;

	public function __construct() {
		$this->id               = 'security-check';
		$this->title            = __( 'Security Check', 'better-wp-security' );
		$this->description      = __( 'Ensure that your site is using the recommended features and settings.', 'better-wp-security' );
		$this->type             = 'recommended';
		$this->information_only = true;
		$this->can_save         = false;

		parent::__construct();
	}

	public function enqueue_scripts_and_styles() {
		$vars = array(
			'securing_site'     => __( 'Securing Site...', 'better-wp-security' ),
			'rerun_secure_site' => __( 'Run Secure Site Again', 'better-wp-security' ),
		);

		wp_enqueue_script( 'itsec-security-check-settings-script', plugins_url( 'js/settings-page.js', __FILE__ ), array( 'jquery' ), $this->script_version, true );
		wp_localize_script( 'itsec-security-check-settings-script', 'itsec_security_check_settings', $vars );
	}

	public function handle_ajax_request( $data ) {
		if ( 'secure-site' === $data['method'] ) {
			if ( ! empty( $data['pro'] ) ) {
				ITSEC_Modules::activate( 'security-check-pro' );
				ITSEC_Modules::load_module_file( 'active.php', 'security-check-pro' );
				ITSEC_Response::remove_js_function_call( 'reloadModule', 'security-check' );
			}

			require_once( dirname( __FILE__ ) . '/scanner.php' );
			require_once( dirname( __FILE__ ) . '/feedback-renderer.php' );

			$results = ITSEC_Security_Check_Scanner::get_results();

			ob_start();
			ITSEC_Security_Check_Feedback_Renderer::render( $results );
			ITSEC_Response::set_response( ob_get_clean() );
		} elseif ( 'activate-network-brute-force' === $data['method'] ) {
			require_once( dirname( __FILE__ ) . '/scanner.php' );

			ITSEC_Security_Check_Scanner::activate_network_brute_force( $_POST['data'] );
		} else {
			do_action( "itsec-security-check-{$data['method']}", $_POST['data'] );
		}
	}

	protected function render_description( $form ) { }

	protected function render_settings( $form ) {
		require_once( dirname( __FILE__ ) . '/scanner.php' );

		$modules_to_activate = ITSEC_Security_Check_Scanner::get_supported_modules();
		?>
		<div id="itsec-security-check-details-container">
			<p><?php _e( 'Some features and settings are recommended for every site to run. This tool will ensure that your site is using these recommendations.', 'better-wp-security' ); ?></p>
			<p><?php _e( 'When the button below is clicked the following modules will be enabled and configured:', 'better-wp-security' ); ?></p>
			<ul class="itsec-security-check-list">
				<?php foreach ( $modules_to_activate as $name ) : ?>
					<li><p><?php echo $name; ?></p></li>
				<?php endforeach; ?>
			</ul>

			<?php if ( ! ITSEC_Modules::is_active( 'security-check-pro' ) ) : ?>
				<p>
					<label>
						<?php $form->add_checkbox( 'security_check_pro' ); ?>
						<?php printf(
							esc_html__( 'Enable Security Check Pro to identify IP addresses and ensure attackers are locked out correctly. This requires contacting an iThemes.com server. Read our %1$sPrivacy Policy%2$s.', 'better-wp-security' ),
							'<a href="https://ithemes.com/privacy-policy/" target="_blank">',
							'</a>'
						); ?>
					</label>
				</p>
			<?php endif; ?>
		</div>
		<p><?php $form->add_button( 'secure_site', array( 'value' => 'Secure Site', 'class' => 'button-primary' ) ); ?></p>
		<?php

	}
}

new ITSEC_Security_Check_Settings_Page();
