<?php

ITSEC_Lib_Admin_Notices::register(
	new ITSEC_Admin_Notice_Globally_Dismissible(
		new ITSEC_Admin_Notice_Managers_Only(
			new class implements ITSEC_Admin_Notice {
				public function get_id() {
					return 'release-turnstile';
				}

				public function get_title() {
					return '';
				}

				public function get_message() {
					return __( 'New Turnstile and hCaptcha Support', 'better-wp-security' );
				}

				public function get_meta() {
					return array();
				}

				public function get_severity() {
					return self::S_INFO;
				}

				public function show_for_context( ITSEC_Admin_Notice_Context $context ) {
					return ITSEC_Core::is_pro();
				}

				public function get_actions() {
					return array(
						'blog' => new ITSEC_Admin_Notice_Action_Link(
							add_query_arg( 'itsec_view_release_post', $this->get_id(), admin_url( 'index.php' ) ),
							esc_html__( 'See What’s New', 'better-wp-security' ),
							ITSEC_Admin_Notice_Action::S_PRIMARY,
							function () {
								$this->handle_dismiss();
								$url = 'https://ithemes.com/?p=82867';

								wp_redirect( $url );
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
		)
	)
);

if ( ! ITSEC_Modules::is_active( 'malware-scheduling' ) ) {
	ITSEC_Lib_Admin_Notices::register(
		new ITSEC_Admin_Notice_Globally_Dismissible(
			new ITSEC_Admin_Notice_Managers_Only(
				new class implements ITSEC_Admin_Notice {
					public function get_id() {
						return 'enable-site-scan';
					}

					public function get_title() {
						return __( 'New! Scheduled Site Scans' );
					}

					public function get_message() {
						return __( 'Enable the Site Scanner to automatically scan your site twice a day for malware and known vulnerabilities.', 'better-wp-security' );
					}

					public function get_meta() {
						return [];
					}

					public function get_severity() {
						return self::S_INFO;
					}

					public function show_for_context( ITSEC_Admin_Notice_Context $context ) {
						return true;
					}

					public function get_actions() {
						return [
							'enable' => new ITSEC_Admin_Notice_Action_Callback(
								ITSEC_Admin_Notice_Action::S_PRIMARY,
								__( 'Enable Scheduling', 'better-wp-security' ),
								function () {
									ITSEC_Modules::activate( 'malware-scheduling' );
								}
							)
						];
					}
				}
			)
		)
	);
}

if ( version_compare( PHP_VERSION, ITSEC_Core::get_next_php_requirement(), '<' ) ) {
	ITSEC_Lib_Admin_Notices::register(
		new ITSEC_Admin_Notice_Remind_Me( new ITSEC_Admin_Notice_Managers_Only( new class implements ITSEC_Admin_Notice {
			public function get_id() {
				return 'php-outdated';
			}

			public function get_title() {
				return sprintf(
					__( 'Your site is running an outdated version of PHP (%1$s). Future versions of Solid Security will require PHP %2$s or later.', 'better-wp-security' ),
					explode( '-', PHP_VERSION )[0],
					ITSEC_Core::get_next_php_requirement()
				);
			}

			public function get_message() {
				return '';
			}

			public function get_meta() {
				return [];
			}

			public function get_severity() {
				return self::S_WARN;
			}

			public function show_for_context( ITSEC_Admin_Notice_Context $context ) {
				return true;
			}

			public function get_actions() {
				$actions = [
					'more' => new ITSEC_Admin_Notice_Action_Link(
						'https://ithemes.com/security/php-requirements/',
						__( 'Learn More', 'better-wp-security' ),
						ITSEC_Admin_Notice_Action::S_PRIMARY
					)
				];

				if ( $direct_update = wp_get_direct_php_update_url() ) {
					$actions['direct_update'] = new ITSEC_Admin_Notice_Action_Link(
						$direct_update,
						__( 'Update PHP', 'better-wp-security' ),
						ITSEC_Admin_Notice_Action::S_PRIMARY
					);
				}

				return $actions;
			}
		} ), WEEK_IN_SECONDS )
	);
}

ITSEC_Lib_Admin_Notices::register(
	new ITSEC_Admin_Notice_Managers_Only( new class implements ITSEC_Admin_Notice {
		public function get_id() {
			return 'encryption-key-changed';
		}

		public function get_title() {
			if ( ITSEC_Lib_Encryption::is_available() ) {
				return __( 'Encryption Key Changed', 'better-wp-security' );
			}

			return __( 'Encryption Key Missing', 'better-wp-security' );
		}

		public function get_message() {
			if ( ITSEC_Lib_Encryption::is_available() ) {
				return __( 'The encryption key for your website was changed. This is used to securely store sensitive values like Two-Factor codes.', 'better-wp-security' );
			}

			return
				sprintf(
					__( 'The encryption key for your website is missing from your %s file.', 'better-wp-security' ),
					'<code>wp-config.php</code>'
				) . ' ' .
				sprintf(
					__( 'If you still have access to your encryption key, add it back to your %s file. Otherwise, set a new encryption key.', 'better-wp-security' ),
					'<code>wp-config.php</code>'
				);
		}

		public function get_meta() {
			return [];
		}

		public function get_severity() {
			return self::S_ERROR;
		}

		public function show_for_context( ITSEC_Admin_Notice_Context $context ) {
			return ITSEC_Lib_Encryption::has_encryption_key_changed();
		}

		public function get_actions() {
			$actions = [
				'dismiss' => new ITSEC_Admin_Notice_Action_Callback(
					ITSEC_Admin_Notice_Action::S_BUTTON,
					__( 'Ignore Change', 'better-wp-security' ),
					function () {
						ITSEC_Lib_Encryption::reset_encryption_key_changed_warning();
					}
				),
			];

			if ( ITSEC_Lib_Encryption::is_available() ) {
				$actions['rotate'] = new ITSEC_Admin_Notice_Action_Link(
					ITSEC_Core::get_admin_page_url( 'tools' ),
					__( 'Rotate Key', 'better-wp-security' ),
					ITSEC_Admin_Notice_Action::S_PRIMARY
				);
			} else {
				$actions['set'] = new ITSEC_Admin_Notice_Action_Link(
					ITSEC_Core::get_admin_page_url( 'tools' ),
					__( 'Set New Key', 'better-wp-security' ),
					ITSEC_Admin_Notice_Action::S_PRIMARY
				);
			}

			return $actions;
		}
	} )
);

ITSEC_Lib_Admin_Notices::register(
	new ITSEC_Admin_Notice_Remind_Me(
		new ITSEC_Admin_Notice_Managers_Only(
			new class implements ITSEC_Admin_Notice {
				public function get_id() {
					return 'setup-encryption';
				}

				public function get_title() {
					return __( 'Solid Security can encrypt sensitive values like Two-Factor secrets.', 'better-wp-security' );
				}

				public function get_message() {
					return '';
				}

				public function get_meta() {
					return [];
				}

				public function get_severity() {
					return self::S_INFO;
				}

				public function show_for_context( ITSEC_Admin_Notice_Context $context ) {
					return ! ITSEC_Lib_Encryption::is_available() &&
					       ! ITSEC_Lib_Encryption::has_encryption_key_changed() &&
					       ITSEC_Files::can_write_to_files();
				}

				public function get_actions() {
					return [
						'setup' => new ITSEC_Admin_Notice_Action_Link(
							ITSEC_Core::get_admin_page_url( 'tools' ),
							__( 'Setup Encryption', 'better-wp-security' ),
							ITSEC_Admin_Notice_Action::S_PRIMARY
						),
					];
				}
			}
		),
		MONTH_IN_SECONDS
	)
);

if ( ITSEC_Modules::get_setting( 'global', 'initial_build' ) < 4127 ) {
	ITSEC_Lib_Admin_Notices::register(
		new ITSEC_Admin_Notice_Globally_Dismissible(
			new ITSEC_Admin_Notice_Managers_Only(
				new class implements ITSEC_Admin_Notice {
					public function get_id() {
						return 'welcome-solidwp';
					}

					public function get_title() {
						return __( 'Welcome to Solid Security', 'better-wp-security' );
					}

					public function get_message() {
						return __( '', 'better-wp-security' );
					}

					public function get_meta() {
						return [];
					}

					public function get_severity() {
						return self::S_INFO;
					}

					public function show_for_context( ITSEC_Admin_Notice_Context $context ) {
						return true;
					}

					public function get_actions() {
						return [
							'open' => new class implements ITSEC_Admin_Notice_Action {
								public function handle( WP_User $user, array $data ) { }

								public function get_title() {
									return __( 'Learn more', 'better-wp-security' );
								}

								public function get_style() {
									return self::S_PRIMARY;
								}

								public function get_uri() {
									return '';
								}

								public function get_route(): string {
									return '';
								}

							}
						];
					}
				}
			)
		)
	);
}
