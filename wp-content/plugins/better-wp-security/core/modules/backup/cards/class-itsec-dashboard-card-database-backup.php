<?php

class ITSEC_Dashboard_Card_Database_Backup extends ITSEC_Dashboard_Card {

	/**
	 * @inheritDoc
	 */
	public function get_slug() {
		return 'database-backup';
	}

	/**
	 * @inheritDoc
	 */
	public function get_label() {
		return __( 'Database Backups', 'better-wp-security' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_size() {
		return array(
			'minW'     => 1,
			'minH'     => 1,
			'maxW'     => 1,
			'maxH'     => 1,
			'defaultW' => 1,
			'defaultH' => 1,
		);
	}

	/**
	 * @inheritDoc
	 */
	public function query_for_data( array $query_args, array $settings ) {

		$dir    = trailingslashit( ITSEC_Modules::get_setting( 'backup', 'location' ) );
		$method = ITSEC_Modules::get_setting( 'backup', 'method' );

		if ( 'email' === $method ) {
			if ( 'file' === ITSEC_Modules::get_setting( 'global', 'log_type' ) ) {
				return array();
			}

			$logs = ITSEC_Log::get_entries( array( 'module' => 'backup' ), 100, 1, 'timestamp', 'DESC', array(
				'code',
				'data',
				'init_timestamp',
			) );

			$backups = array();

			foreach ( $logs as $log ) {
				$size = empty( $log['data']['size'] ) ? false : $log['data']['size'];

				$backups[] = array(
					'time'        => ITSEC_Lib::to_rest_date( $log['init_timestamp'] ),
					'size'        => $size,
					'size_format' => $size ? size_format( $size, 2 ) : __( 'unknown', 'better-wp-security' ),
					'url'         => false,
				);
			}

			return array(
				'total'   => count( $backups ),
				'backups' => $backups,
				'source'  => 'logs',
			);
		}

		if ( ! $dir || ! @file_exists( $dir ) ) {
			return new WP_Error( 'itsec-dashboard-card-database-backup-invalid-dir', esc_html__( 'Invalid Backups Directory', 'better-wp-security' ) );
		}

		$backups = array();

		$files = scandir( $dir, SCANDIR_SORT_DESCENDING );
		$files = array_unique( $files );

		foreach ( $files as $file ) {
			if ( 0 === strpos( $file, 'backup-' ) ) {
				$backups[] = $this->format_backup( $file, $dir );
			}
		}

		return array(
			'total'   => count( $backups ),
			'backups' => wp_list_sort( array_slice( $backups, 0, 100 ), 'time', 'DESC' ),
			'source'  => 'files',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_links() {
		return array(
			array(
				'rel'   => ITSEC_Lib_REST::LINK_REL . 'logs',
				'href'  => ITSEC_Core::get_logs_page_url( array( 'module' => 'backup' ) ),
				'title' => __( 'View Logs', 'better-wp-security' ),
				'media' => 'text/html',
				'cap'   => ITSEC_Core::get_required_cap(),
			),
			array(
				'rel'      => ITSEC_Lib_REST::LINK_REL . 'rpc',
				'title'    => __( 'Backup Now', 'better-wp-security' ),
				'endpoint' => 'backup',
				'cap'      => ITSEC_Core::get_required_cap(),
				'callback' => array( $this, 'do_backup' ),
			)
		);
	}

	public function do_backup() {
		global $itsec_backup;

		if ( null === $itsec_backup ) {
			ITSEC_Modules::load_module_file( 'class-itsec-backup.php' );
			$itsec_backup = new ITSEC_Backup();
			$itsec_backup->run();
		}

		$result = $itsec_backup->do_backup( true );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( is_array( $result ) ) {
			return array(
				'message' => $result['message'],
				'backup'  => array(
					'time'        => ITSEC_Lib::to_rest_date(),
					'size'        => $result['size'],
					'size_format' => $result['size'] ? size_format( $result['size'], 2 ) : __( 'unknown', 'better-wp-security' ),
					'url'         => ITSEC_Lib::get_url_from_file( $result['output_file'] ),
				),
			);
		}

		return new WP_Error( 'itsec-dashboard-card-backup-unexpected-response', __( 'The backup request returned an unexpected response.', 'better-wp-security' ) );
	}

	/**
	 * Format a backup file to an array.
	 *
	 * @param string $file
	 * @param string $dir
	 *
	 * @return array
	 */
	private function format_backup( $file, $dir ) {

		$path = trailingslashit( $dir ) . $file;
		list( , $time, $day ) = array_reverse( explode( '-', $file ) );

		$epoch = strtotime( $day . ' ' . $time );
		$size  = @filesize( $path );

		return array(
			'time'        => ITSEC_Lib::to_rest_date( $epoch ),
			'size'        => $size,
			'size_format' => $size ? size_format( $size, 2 ) : __( 'unknown', 'better-wp-security' ),
			'url'         => ITSEC_Lib::get_url_from_file( $path ),
		);
	}
}
