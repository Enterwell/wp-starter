<?php

require_once( dirname( __FILE__ ) . '/admin-notices/class-itsec-admin-notice-context.php' );
require_once( dirname( __FILE__ ) . '/admin-notices/interface-itsec-admin-notice.php' );
require_once( dirname( __FILE__ ) . '/admin-notices/class-itsec-admin-notice-callback.php' );
require_once( dirname( __FILE__ ) . '/admin-notices/class-itsec-admin-notice-globally-dismissible.php' );
require_once( dirname( __FILE__ ) . '/admin-notices/class-itsec-admin-notice-highlighted-log.php' );
require_once( dirname( __FILE__ ) . '/admin-notices/class-itsec-admin-notice-managers-only.php' );
require_once( dirname( __FILE__ ) . '/admin-notices/class-itsec-admin-notice-remind-me.php' );
require_once( dirname( __FILE__ ) . '/admin-notices/class-itsec-admin-notice-screen-blacklist.php' );
require_once( dirname( __FILE__ ) . '/admin-notices/class-itsec-admin-notice-static.php' );
require_once( dirname( __FILE__ ) . '/admin-notices/class-itsec-admin-notice-user-dismissible.php' );

require_once( dirname( __FILE__ ) . '/admin-notices/actions/interface-itsec-admin-notice-action.php' );
require_once( dirname( __FILE__ ) . '/admin-notices/actions/class-itsec-admin-notice-action-link.php' );
require_once( dirname( __FILE__ ) . '/admin-notices/actions/class-itsec-admin-notice-action-callback.php' );

class ITSEC_Lib_Admin_Notices {

	const META = '_itsec_dismissed_notices';

	/** @var ITSEC_Admin_Notice[] */
	private static $notices = array();

	/** @var bool */
	private static $initialized = false;

	/**
	 * Register a notice to be displayed.
	 *
	 * @param ITSEC_Admin_Notice $notice
	 */
	public static function register( ITSEC_Admin_Notice $notice ) {
		self::$notices[] = $notice;
	}

	/**
	 * Get all notices for the given user.
	 *
	 * @param ITSEC_Admin_Notice_Context $context
	 *
	 * @return ITSEC_Admin_Notice[]
	 */
	public static function get_notices( ITSEC_Admin_Notice_Context $context = null ) {
		self::initialize();

		$context = $context ? $context : ITSEC_Admin_Notice_Context::from_global_state();
		$notices = array();

		foreach ( self::$notices as $notice ) {
			if ( $notice->show_for_context( $context ) ) {
				$notices[] = $notice;
			}
		}

		return $notices;
	}

	/**
	 * Lazily initialize the admin notices lib.
	 *
	 * Will load the notices.php file for all active modules, and create notices
	 * for each highlighted log item.
	 */
	private static function initialize() {
		if ( ! self::$initialized ) {
			ITSEC_Lib::load( 'highlighted-logs' );

			foreach ( ITSEC_Lib_Highlighted_Logs::get_highlights() as $id => $highlight ) {
				self::register(
					new ITSEC_Admin_Notice_Managers_Only(
						new ITSEC_Admin_Notice_Screen_Blacklist(
							new ITSEC_Admin_Notice_Highlighted_Log( $id, $highlight ),
							array( 'security_page_itsec-logs' )
						)
					)
				);
			}

			ITSEC_Modules::load_module_file( 'notices.php', ':active' );

			self::$initialized = true;
		}
	}
}
