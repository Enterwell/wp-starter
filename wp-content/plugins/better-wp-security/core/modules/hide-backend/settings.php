<?php

use iThemesSecurity\Config_Settings;

final class ITSEC_Hide_Backend_Settings extends Config_Settings {
	protected function handle_settings_changes( $old_settings ) {
		parent::handle_settings_changes( $old_settings );

		if ( $this->settings['enabled'] && ! $old_settings['enabled'] ) {
			$url      = get_site_url() . '/' . $this->settings['slug'];
			$enabling = true;
			$message  = __( 'The Hide Backend feature is now active.', 'better-wp-security' );
		} elseif ( ! $this->settings['enabled'] && $old_settings['enabled'] ) {
			$url      = get_site_url() . '/wp-login.php';
			$enabling = false;
			$message  = __( 'The Hide Backend feature is now disabled', 'better-wp-security' );
		} elseif ( $this->settings['enabled'] && $this->settings['slug'] !== $old_settings['slug'] ) {
			$url      = get_site_url() . '/' . $this->settings['slug'];
			$enabling = false;
			$message  = __( 'The Hide Backend feature is now active.', 'better-wp-security' );
		} else {
			return;
		}

		ITSEC_Response::add_message( $message );
		ITSEC_Response::add_message( sprintf( __( 'Your new login URL is <strong><code>%1$s</code></strong>. A reminder has also been sent to the notification email addresses set in Solid Security’s Notification Center.', 'better-wp-security' ), esc_url( $url ) ) );
		$this->send_new_login_url( $url, $enabling );
	}

	private function send_new_login_url( $url, $enabling ) {
		if ( ITSEC_Core::doing_data_upgrade() ) {
			// Do not send emails when upgrading data. This prevents spamming users with notifications just because the
			// data was ported from an old version to a new version.
			return;
		}

		$nc = ITSEC_Core::get_notification_center();

		if ( $enabling ) {
			$nc->clear_notifications_cache();
			ITSEC_Modules::get_settings_obj( 'notification-center' )->load();
		}

		$mail = $nc->mail();

		$mail->add_header(
			esc_html__( 'New Login URL', 'better-wp-security' ),
			esc_html__( 'New Login URL', 'better-wp-security' ),
			false,
			esc_html__( 'Your new login URL is available below', 'better-wp-security' ),
		);
		$mail->add_text( ITSEC_Lib::replace_tags( $nc->get_message( 'hide-backend' ), array(
			'login_url'  => '<code>' . esc_url( $url ) . '</code>',
			'site_title' => get_bloginfo( 'name', 'display' ),
			'site_url'   => $mail->get_display_url(),
		) ) );
		$mail->add_button( esc_html__( 'Login Now', 'better-wp-security' ), $url );
		$mail->add_footer();

		$subject = $mail->prepend_site_url_to_subject( $nc->get_subject( 'hide-backend' ) );
		$subject = apply_filters( 'itsec_hide_backend_email_subject', $subject );
		$mail->set_subject( $subject, false );
		$nc->send( 'hide-backend', $mail );
	}
}

ITSEC_Modules::register_settings( new ITSEC_Hide_Backend_Settings( ITSEC_Modules::get_config( 'hide-backend' ) ) );
