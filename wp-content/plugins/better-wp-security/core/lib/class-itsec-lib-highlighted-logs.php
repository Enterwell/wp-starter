<?php

class ITSEC_Lib_Highlighted_Logs {

	const OPTION = 'itsec_highlighted_logs';

	/** @var array */
	private static $dynamics = array();

	/** @var bool */
	private static $initialized = false;

	/**
	 * Is the highlighted logs library supported.
	 *
	 * This can only work when log items are stored in the database.
	 *
	 * @return bool
	 */
	public static function is_supported() {
		return 'file' !== ITSEC_Modules::get_setting( 'global', 'log_type' );
	}

	/**
	 * Register a dynamic highlight.
	 *
	 * Dynamic highlights display the latest item that matches the given query.
	 * Only log items that are more recent since the dynamic highlight has been
	 * dismissed are included.
	 *
	 * @param string $slug  Unique identifier for this highlight.
	 * @param array  $query Filters list.
	 */
	public static function register_dynamic_highlight( $slug, array $query ) {
		self::$dynamics[ $slug ] = $query;
	}

	/**
	 * Highlight an individual log item.
	 *
	 * @param int $id The log item id.
	 *
	 * @return bool
	 */
	public static function highlight( $id ) {
		$storage = self::get_storage();

		$storage['highlighted'][ $id ] = true;

		return self::save_storage( $storage );
	}

	/**
	 * Get a list of all the log items that are highlighted.
	 *
	 * The full log data entry is not available, just the summary columns
	 *
	 * @return array[]
	 */
	public static function get_highlights() {
		$storage = self::get_storage();

		$items = array();

		foreach ( self::get_dynamics() as $slug => $highlight ) {
			if ( isset( $storage['muted'][ $slug ] ) ) {
				continue;
			}

			$filters = $highlight;

			if ( isset( $storage['markers'][ $slug ] ) ) {
				$filters['__min_timestamp'] = $storage['markers'][ $slug ];
			}

			if ( $entries = ITSEC_Log::get_entries( $filters, 1, 1, 'id', 'DESC', 'all' ) ) {
				$items[ $slug ] = $entries[0];
			}
		}

		if ( $storage['highlighted'] ) {
			$entries = ITSEC_Log::get_entries( array( 'id' => array_flip( $storage['highlighted'] ) ) );

			$items = array_merge( $items, $entries );
		}

		return $items;
	}

	/**
	 * Dismiss a highlighted log item.
	 *
	 * @param int|string $id_or_slug Either the log id or the dynamic highlight slug.
	 *
	 * @return bool
	 */
	public static function dismiss_highlight( $id_or_slug ) {
		$storage = self::get_storage();

		if ( is_int( $id_or_slug ) ) {
			if ( isset( $storage['highlighted'][ $id_or_slug ] ) ) {
				unset( $storage['highlighted'][ $id_or_slug ] );

				return self::save_storage( $storage );
			}

			return true;
		}

		$storage['markers'][ $id_or_slug ] = ITSEC_Core::get_current_time_gmt();

		return self::save_storage( $storage );
	}

	/**
	 * Mute a dynamic highlighted log.
	 *
	 * Muted items won't ever be returned in {@see ITSEC_Lib_Highlighted_Logs::get_highlights()}.
	 *
	 * @param string $slug Dynamic highlight slug.
	 *
	 * @return bool
	 */
	public static function mute( $slug ) {
		$storage = self::get_storage();

		$storage['muted'][ $slug ] = ITSEC_Core::get_current_time_gmt();

		return self::save_storage( $storage );
	}

	/**
	 * Unmute a dynamic highlighted log.
	 *
	 * @param string $slug Dynamic highlight slug.
	 *
	 * @return bool
	 */
	public static function unmute( $slug ) {
		$storage = self::get_storage();

		unset( $storage['muted'][ $slug ] );

		return self::save_storage( $storage );
	}

	/**
	 * Is the given dynamic highlight muted.
	 *
	 * @param string $slug
	 *
	 * @return bool
	 */
	public static function is_muted( $slug ) {
		$storage = self::get_storage();

		return ! empty( $storage['muted'][ $slug ] );
	}

	/**
	 * Get a list of all the registered dynamic highlights.
	 *
	 * On first call, this will fire an action to register the highlights.
	 *
	 * @return array
	 */
	public static function get_dynamics() {
		if ( ! self::$initialized ) {
			do_action( 'itsec_register_highlighted_logs' );

			self::$initialized = true;
		}

		return self::$dynamics;
	}

	/**
	 * Get the storage data.
	 *
	 * @return array
	 */
	private static function get_storage() {
		return get_site_option( self::OPTION, array(
			'highlighted' => array(),
			'markers'     => array(),
			'muted'       => array(),
		) );
	}

	/**
	 * Update the storage data.
	 *
	 * @param array $storage
	 *
	 * @return bool
	 */
	private static function save_storage( array $storage ) {
		return update_site_option( self::OPTION, $storage );
	}
}
