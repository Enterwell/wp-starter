<?php

class ITSEC_Two_Factor_Interstitial extends ITSEC_Login_Interstitial {

	const SAME_BROWSER_COOKIE_NAME = 'itsec-2fa-same-session';
	const SAME_BROWSER_ACTION = '2fa-same-session';

	/** @var ITSEC_Two_Factor */
	private $two_factor;

	/** @var Two_Factor_Provider */
	private $failed_provider;

	/** @var string */
	private $current_provider_class;

	/**
	 * ITSEC_Two_Factor_Interstitial constructor.
	 *
	 * @param ITSEC_Two_Factor $two_factor
	 */
	public function __construct( ITSEC_Two_Factor $two_factor ) { $this->two_factor = $two_factor; }

	/**
	 * Run initialization code for the interstitial.
	 */
	public function run() {
		add_action( 'itsec_two_factor_override', array( $this, 'proceed_on_override' ) );
	}

	/**
	 * Proceed to the next interstitial when the user's 2fa code is overridden.
	 *
	 * @param WP_User $user
	 */
	public function proceed_on_override( $user ) {
		if ( ! $user instanceof WP_User ) {
			return;
		}

		foreach ( ITSEC_Login_Interstitial_Session::get_all( $user ) as $session ) {
			if ( '2fa' === $session->get_current_interstitial() ) {
				ITSEC_Core::get_login_interstitial()->proceed_to_next( $session );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function render( ITSEC_Login_Interstitial_Session $session, array $args ) {

		$user = $session->get_user();

		$available_providers = $this->two_factor->get_available_providers_for_user( $user );

		if ( ! $provider = $this->get_provider( $session ) ) {
			echo '<div style="background-color: #fbeaea;border-left: 4px solid #dc3232;padding: 6px 12px;display: block;margin: 0 0 1.5em 0;"><p>';
			printf( esc_html__( 'Invalid Two-Factor provider. Please try %1$slogging in again%2$s.', 'better-wp-security' ), '<a href="' . esc_url( $args['wp_login_url'] ) . '">', '</a>' );
			echo '</p></div>';

			return;
		}

		$provider_class   = get_class( $provider );
		$backup_providers = array_diff_key( $available_providers, array( $provider_class => null ) );
		?>
		<input type="hidden" name="provider" id="provider" value="<?php echo esc_attr( $provider_class ); ?>"/>
		<?php $provider->authentication_page( $user ); ?>

		<?php do_action( 'itsec_two_factor_interstitial_after_auth_page', $provider, $session, $args ); ?>

		<?php if ( $provider->can_resend_code() ): ?>
			<div style="clear:both;margin-top:3em;padding-top:1em;border-top:1px solid #ddd;margin-bottom: -2em">
				<p style="margin-bottom: 1em;"><?php esc_html_e( 'Did your Two-Factor code not arrive?', 'better-wp-security' ); ?></p>
				<button class="button" name="itsec_resend_2fa" type="submit" value="1"><?php esc_html_e( 'Resend', 'better-wp-security' ); ?></button>
			</div>
		<?php endif; ?>

		<?php if ( $backup_providers ) : ?>
			<div class="itsec-backup-methods" style="clear:both;margin-top:3em;padding-top:1em;border-top:1px solid #ddd;margin-bottom:-2em;padding-bottom:2rem;">
				<p><?php esc_html_e( 'Or, use a backup method:', 'better-wp-security' ); ?></p>
				<ul style="margin-left:1em;">
					<?php foreach ( $backup_providers as $backup_classname => $backup_provider ) : ?>
						<li>
							<a href="<?php echo esc_url( add_query_arg( urlencode_deep( array(
								'action'                                => 'itsec-2fa',
								'provider'                              => $backup_classname,
								ITSEC_Lib_Login_Interstitial::R_USER    => $user->ID,
								ITSEC_Lib_Login_Interstitial::R_TOKEN   => $session->get_signature(),
								ITSEC_Lib_Login_Interstitial::R_SESSION => $session->get_id(),
							) ), $args['wp_login_url'] ) ); ?>">
								<?php $backup_provider->print_label(); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
		<?php
	}

	public function pre_render( ITSEC_Login_Interstitial_Session $session ) {
		if ( $provider = $this->get_provider( $session ) ) {
			$provider->pre_render_authentication_page( $session->get_user() );
			do_action( 'itsec_two_factor_interstitial_pre_render', $session, $provider );
		}
	}

	/**
	 * Get the provider to use.
	 *
	 * @param ITSEC_Login_Interstitial_Session $session
	 *
	 * @return Two_Factor_Provider|null
	 */
	private function get_provider( ITSEC_Login_Interstitial_Session $session ) {
		$available_providers = $this->two_factor->get_available_providers_for_user( $session->get_user() );

		$provider = empty( $_GET['provider'] ) ? '' : $_GET['provider'];

		if ( ! $provider ) {
			$provider = $this->two_factor->get_primary_provider_for_user( $session->get_user()->ID );
		} elseif ( is_string( $provider ) ) {
			if ( ! isset( $available_providers[ $provider ] ) || ! method_exists( $provider, 'get_instance' ) ) {
				return null;
			}

			$provider = call_user_func( array( $provider, 'get_instance' ) );
		}

		return $provider;
	}

	public function has_submit() {
		return true;
	}

	public function submit( ITSEC_Login_Interstitial_Session $session, array $post_data ) {

		$user    = $session->get_user();
		$user_id = $user->ID;

		if ( isset( $post_data['provider'] ) ) {
			$providers = $this->two_factor->get_available_providers_for_user( $user );
			if ( isset( $providers[ $post_data['provider'] ] ) ) {
				$provider = $providers[ $post_data['provider'] ];
			} else {
				ITSEC_Log::add_debug( 'two_factor', "failed_authentication::$user_id,missing_provider", compact( 'user_id', 'post_data' ), compact( 'user_id' ) );

				return new WP_Error(
					'itsec-two-factor-missing-provider',
					esc_html__( 'Invalid Two Factor provider.', 'better-wp-security' )
				);
			}
		} else {
			$provider = $this->two_factor->get_primary_provider_for_user( $user->ID );
		}

		if ( isset( $post_data['itsec_resend_2fa'] ) ) {
			return new WP_Error(
				'itsec-two-factor-resent-2fa',
				esc_html__( 'Two-Factor code resent.', 'better-wp-security' ),
				'message'
			);
		}

		$provider_class = get_class( $provider );
		$user_id        = $user->ID;

		if ( true !== $provider->validate_authentication( $user ) ) {
			ITSEC_Log::add_debug( 'two_factor', "failed_authentication::$user_id,$provider_class,invalid_code", compact( 'user_id', 'provider_class', 'post_data' ), compact( 'user_id' ) );

			$this->failed_provider = $provider;
			add_filter( 'itsec-filter-failed-login-details', array( $this, 'filter_failed_login_details' ) );

			$error = new WP_Error(
				'itsec-two-factor-invalid-code',
				esc_html__( 'ERROR: Invalid Authentication Code.', 'better-wp-security' )
			);

			do_action( 'wp_login_failed', $user->user_login, $error );

			return $error;
		}

		$this->current_provider_class = $provider_class;

		return null;
	}

	public function has_async_action() {
		return true;
	}

	public function handle_async_action( ITSEC_Login_Interstitial_Session $session, $action, array $args ) {
		if ( '2fa-verify-email' !== $action ) {
			return null;
		}

		ITSEC_Core::get_login_interstitial()->proceed_to_next( $session );

		return array(
			'message' => esc_html__( 'Login authorized. Please continue in your original browser.', 'better-wp-security' ),
		);
	}

	public function after_submit( ITSEC_Login_Interstitial_Session $session, array $post_data ) {

		$user_id        = $session->get_user()->ID;
		$provider_class = $this->current_provider_class;

		ITSEC_Log::add_debug( 'two_factor', "successful_authentication::$user_id,$provider_class", compact( 'user_id', 'provider_class', 'post_data' ), compact( 'user_id' ) );
		do_action( 'itsec-two-factor-successful-authentication', $user_id, $provider_class, $post_data );
	}

	public function show_to_user( WP_User $user, $is_requested ) {

		if ( ! $this->two_factor->is_user_using_two_factor( $user->ID ) ) {
			return false;
		}

		if ( $this->two_factor->is_sync_override_active( $user->ID ) ) {
			// Sync override is active. Do not request the authentication code.
			return false;
		}

		if ( did_action( 'jetpack_sso_handle_login' ) ) {
			// This is a Jetpack Single Sign On login.
			return false;
		}

		if ( ITSEC_Modules::get_setting( 'two-factor', 'disable_first_login' ) && ! get_user_meta( $user->ID, '_itsec_has_logged_in', true ) ) {
			return false;
		}

		/**
		 * Filters whether the Two-Factor interstitial should be shown to the given user.
		 *
		 * @param bool    $show_to_user
		 * @param WP_User $user
		 * @param bool    $is_requested
		 */
		return apply_filters( 'itsec_two_factor_interstitial_show_to_user', true, $user, $is_requested );
	}

	public function get_priority() {
		return 1;
	}

	/**
	 * Filter the failed login details.
	 *
	 * @param array $details
	 *
	 * @return array
	 */
	public function filter_failed_login_details( $details ) {
		if ( empty( $this->failed_provider ) ) {
			$details['authentication_types'] = array( __( 'unknown_two_factor_provider', 'better-wp-security' ), 'unknown' );
		} else {
			$details['authentication_types'] = array( $this->failed_provider->get_label(), get_class( $this->failed_provider ) );
		}

		return $details;
	}
}
