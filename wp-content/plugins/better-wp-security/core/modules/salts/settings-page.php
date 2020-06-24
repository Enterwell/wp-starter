<?php

final class ITSEC_WordPress_Salts_Settings_Page extends ITSEC_Module_Settings_Page {
	public function __construct() {
		$this->id = 'wordpress-salts';
		$this->title = __( 'WordPress Salts', 'better-wp-security' );
		$this->description = __( 'Update the secret keys WordPress uses to increase the security of your site.', 'better-wp-security' );
		$this->type = 'recommended';

		require_once( __DIR__ . '/utilities.php' );

		if ( ! ITSEC_WordPress_Salts_Utilities::check_valid_salts() ) {
			$this->status = 'warning';
		}

		parent::__construct();
	}

	protected function render_description( $form ) {

?>
	<p><?php esc_html_e( 'A secret key makes your site harder to hack and access by adding random elements to the password.', 'better-wp-security' ) ?></p>
	<p><?php esc_html_e( 'In simple terms, a secret key is a password with elements that make it harder to generate enough options to break through your security barriers. A password like "password" or "test" is simple and easily broken. A random, unpredictable password such as "88a7da62429ba6ad3cb3c76a09641fc" takes years to come up with the right combination. A salt is used to further enhance the security of the generated result.', 'better-wp-security' ); ?></p>
<?php
	}

	protected function render_settings( $form ) {

		if ( $this->status === 'warning' ) {
			?>
			<div class="notice notice-warning notice-alt below-h2">
				<p>
					<?php printf(
						esc_html__( 'Currently one or more of your salts is invalid. Please either use the tool below, or %1$smanually update your wp-config.php file%2$s.', 'better-wp-security' ),
						'<a href="https://ithemes.com/easily-change-wordpress-security-keys-salts-ithemes-security-plugin/">',
						'</a>'
					); ?>
				</p>
			</div>
			<?php
		}

?>
	<div class="itsec-write-files-enabled">
		<p><strong><?php esc_html_e( 'Note that changing the salts will log you out of your WordPress site.', 'better-wp-security' ) ?></strong></p>
		<table class="form-table itsec-settings-section">
			<tr>
				<th scope="row"><label for="itsec-wordpress-salts-regenerate"><?php _e( 'Change WordPress Salts', 'better-wp-security' ); ?></label></th>
				<td>
					<?php $form->add_checkbox( 'regenerate' ); ?>
					<br />
					<p class="description"><?php _e( 'Check this box and then save settings to change your WordPress Salts.', 'better-wp-security' ); ?></p>
				</td>
			</tr>
		</table>
	</div>
	<div class="itsec-write-files-disabled">
		<div class="itsec-warning-message"><?php _e( 'The "Write to Files" setting is disabled in Global Settings. In order to use this feature, you must enable the "Write to Files" setting.', 'better-wp-security' ); ?></div>
	</div>
<?php

	}
}

new ITSEC_WordPress_Salts_Settings_Page();
