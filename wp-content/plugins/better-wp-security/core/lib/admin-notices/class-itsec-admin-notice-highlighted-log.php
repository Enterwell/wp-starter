<?php

class ITSEC_Admin_Notice_Highlighted_Log implements ITSEC_Admin_Notice {

	private $highlight_id;
	private $log;
	private $filtered;

	/**
	 * ITSEC_Admin_Notice_Highlighted_Log constructor.
	 *
	 * @param string|int $id_or_slug
	 * @param array      $log_item
	 */
	public function __construct( $id_or_slug, array $log_item ) {
		$this->highlight_id = $id_or_slug;
		$this->log          = $log_item;
	}

	public function get_id() {
		return "highlighted-log-{$this->highlight_id}";
	}

	public function get_title() {
		ITSEC_Modules::load_module_file( 'logs.php' );

		return apply_filters( "itsec_highlighted_log_{$this->highlight_id}_notice_title", '', $this->log );
	}

	public function get_message() {
		ITSEC_Modules::load_module_file( 'logs.php' );
		$filtered = $this->get_filtered_log();

		$message = '<strong>' . esc_html( $filtered['module_display'] ) . '</strong>: ' . $filtered['description'];

		return apply_filters( "itsec_highlighted_log_{$this->highlight_id}_notice_message", $message, $this->log );
	}

	public function get_severity() {
		switch ( $this->log['type'] ) {
			case 'critical-issue':
			case 'fatal':
				return self::S_ERROR;
			case 'error':
			case 'warning':
				return self::S_WARN;
			case 'action':
				return self::S_SUCCESS;
			default:
				return self::S_INFO;
		}
	}

	public function get_meta() {
		$filtered = $this->get_filtered_log();

		return array(
			self::M_CREATED_AT => array(
				'label'     => esc_html__( 'Created At', 'better-wp-security' ),
				'value'     => $this->log['init_timestamp'],
				'formatted' => sprintf(
					'%s – %s ago',
					date_i18n( 'M d, Y g:i A', strtotime( $this->log['init_timestamp'] ) ),
					human_time_diff( strtotime( $this->log['init_timestamp'] ) )
				),
			),
			'module'           => array(
				'label'     => esc_html__( 'Module', 'better-wp-security' ),
				'value'     => $this->log['module'],
				'formatted' => $filtered['module_display'],
			),
			'description'      => array(
				'label'     => esc_html__( 'Description', 'better-wp-security' ),
				'value'     => $this->log['code'],
				'formatted' => $filtered['description'],
			),
		);
	}

	public function show_for_context( ITSEC_Admin_Notice_Context $context ) {
		return true;
	}

	public function get_actions() {
		$actions = array(
			'dismiss' => new ITSEC_Admin_Notice_Action_Callback(
				ITSEC_Admin_Notice_Action::S_CLOSE,
				esc_html__( 'Dismiss', 'better-wp-security' ),
				array( $this, '_handle_dismiss' )
			),
			'view'    => new ITSEC_Admin_Notice_Action_Link(
				add_query_arg( 'id', $this->log['id'], ITSEC_Core::get_logs_page_url() ),
				esc_html__( 'View Details', 'better-wp-security' ),
				ITSEC_Admin_Notice_Action::S_LINK,
				array( $this, '_handle_dismiss' )
			)
		);

		if ( ! is_numeric( $this->highlight_id ) ) {
			$actions['mute'] = new ITSEC_Admin_Notice_Action_Callback(
				ITSEC_Admin_Notice_Action::S_BUTTON,
				esc_html__( 'Dismiss Permanently', 'better-wp-security' ),
				array( $this, '_handle_mute' )
			);
		}

		return $actions;
	}

	public function _handle_mute( WP_User $user, array $data ) {
		$this->_handle_dismiss( $user, $data );
		ITSEC_Lib_Highlighted_Logs::mute( $this->highlight_id );
	}

	public function _handle_dismiss( WP_User $user, array $data ) {
		ITSEC_Lib_Highlighted_Logs::dismiss_highlight( $this->highlight_id );
	}

	private function get_filtered_log() {
		if ( $this->filtered ) {
			return $this->filtered;
		}

		$item = $this->log;

		if ( false === strpos( $item['code'], '::' ) ) {
			$code = $item['code'];
			$data = array();
		} else {
			list( $code, $data ) = explode( '::', $item['code'], 2 );
			$data = explode( ',', $data );
		}

		$item['description']    = $item['code'];
		$item['module_display'] = $item['module'];

		ITSEC_Modules::load_module_file( 'logs.php' );
		$item = apply_filters( "itsec_logs_prepare_{$item['module']}_entry_for_list_display", $item, $code, $data );

		return $this->filtered = $item;
	}
}
