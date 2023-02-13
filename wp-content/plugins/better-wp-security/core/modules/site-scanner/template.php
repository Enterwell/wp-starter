<?php

use iThemesSecurity\Site_Scanner\Entry;
use iThemesSecurity\Site_Scanner\Factory;
use iThemesSecurity\Site_Scanner\Scan;

class ITSEC_Site_Scanner_Template {

	private static $instance_id = 0;

	/**
	 * Get's the HTML for the scan results.
	 *
	 * @param Scan|array|\WP_Error $results
	 * @param bool                 $show_error_details
	 *
	 * @return string
	 */
	public static function get_html( $results, $show_error_details = true ) {
		if ( $results instanceof Scan ) {
			$scan = $results;
		} else {
			$scan = ITSEC_Modules::get_container()->get( Factory::class )->for_api_response( $results );
		}

		$html = '<div class="itsec-site-scan-results">';

		if ( self::show_site_url( $scan ) ) {
			$html .= '<h4>' . sprintf( esc_html__( 'Site: %s', 'better-wp-security' ), $scan->get_url() ) . '</h4>';
		}

		if ( $scan->is_error() ) {
			$html .= self::render_wp_error_details( $scan->get_error(), $show_error_details );
		} else {
			$html .= self::render_system_error_details( $scan );

			foreach ( $scan->get_entries() as $entry ) {
				$html .= self::render_entry( $entry );
			}
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Renders an entry.
	 *
	 * @param Entry $entry
	 *
	 * @return string
	 */
	private static function render_entry( Entry $entry ) {
		$children = '';

		foreach ( $entry->get_issues() as $issue ) {
			$children .= '<li class="itsec-site-scan__detail itsec-site-scan__detail--' . esc_attr( $issue->get_status() ) . '"><span>';
			$children .= '<a href="' . esc_url( $issue->get_link() ) . '">';
			$children .= $issue->get_description();
			$children .= '</a>';
			$children .= '</span></li>';
		}

		return self::render_wrapped_section( [
			'type'        => $entry->get_slug(),
			'status'      => $entry->get_status(),
			'description' => $entry->get_title(),
			'children'    => $children,
		] );
	}

	/**
	 * Render details for a system error.
	 *
	 * @param Scan $scan
	 *
	 * @return string
	 */
	private static function render_system_error_details( Scan $scan ) {
		if ( ! $scan->get_errors() ) {
			return '';
		}

		$children = '';

		foreach ( $scan->get_errors() as $error ) {
			$children .= '<li class="itsec-site-scan__detail itsec-site-scan__detail--error"><span>' . esc_html( $error['message'] ) . '</span></li>';
		}

		return self::render_wrapped_section( array(
			'children'    => $children,
			'type'        => 'system-error',
			'status'      => 'error',
			'description' => esc_html__( 'The scan failed to properly scan the site.', 'better-wp-security' ),
		) );
	}

	/**
	 * Render details for a WP_Error.
	 *
	 * @param WP_Error $results
	 * @param bool     $show_error_details
	 *
	 * @return string
	 */
	private static function render_wp_error_details( $results, $show_error_details ) {
		$html = '<p>' . sprintf( esc_html__( 'Error Message: %s', 'better-wp-security' ), implode( ' ', ITSEC_Lib::get_error_strings( $results ) ) ) . '</p>';
		$html .= '<p>' . sprintf( esc_html__( 'Error Code: %s', 'better-wp-security' ), $results->get_error_code() ) . '</p>';

		if ( $show_error_details && $results->get_error_data() ) {
			$html .= '<p>' . esc_html__( 'If you contact support about this error, please provide the following debug details:', 'better-wp-security' ) . '</p>';
			$html .= ITSEC_Debug::print_r( array(
				'code' => $results->get_error_code(),
				'data' => $results->get_error_data(),
			), [], false );
		}

		return self::render_wrapped_section( array(
			'children'    => $html,
			'type'        => 'wp-error',
			'status'      => 'error',
			'description' => esc_html__( 'The scan failed to properly scan the site.', 'better-wp-security' ),
		) );
	}

	/**
	 * Render wrapped section HTML.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	private static function render_wrapped_section( $args ) {
		$i_id = self::$instance_id ++;

		switch ( $args['status'] ) {
			case 'clean':
				$status_text = __( 'Clean', 'better-wp-security' );
				break;
			case 'warn':
				$status_text = __( 'Warn', 'better-wp-security' );
				break;
			case 'error':
				$status_text = __( 'Error', 'better-wp-security' );
				break;
			default:
				$status_text = $args['status'];
				break;
		}

		$status_el = '<span class="itsec-site-scan__status itsec-site-scan__status--' . esc_attr( $args['status'] ) . '">' . $status_text . '</span>';

		$html = '<div class="itsec-site-scan-results-section itsec-site-scan-results-' . esc_attr( $args['type'] ) . '-section">';

		if ( empty( $args['children'] ) ) {
			$html .= '<p>' . $status_el . ' ' . esc_html( $args['description'] ) . '</p>';
		} else {
			$html .= '<p>';
			$html .= $status_el;
			$html .= esc_html( $args['description'] );

			$id = 'itsec-site-scan__details--' . $i_id;

			$html .= '<button type="button" class="itsec-site-scan-toggle-details button-link" aria-expanded="false" aria-controls="' . esc_attr( $id ) . '">';
			$html .= esc_html__( 'Show Details', 'better-wp-security' );
			$html .= '</button>';
			$html .= '</p>';

			$html .= '<div class="itsec-site-scan__details hidden" id="' . esc_attr( $id ) . '">';
			$html .= '<ul>';
			$html .= $args['children'];
			$html .= '</ul>';
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Should the site URL be showed.
	 *
	 * @param Scan $scan
	 *
	 * @return bool
	 */
	private static function show_site_url( Scan $scan ) {
		if ( ! $scan->get_url() ) {
			return false;
		}

		$cleaned_scan = preg_replace( '/https?:\/\//', '', $scan->get_url() );
		$cleaned_home = preg_replace( '/https?:\/\//', '', network_home_url() );

		return $cleaned_scan !== $cleaned_home;
	}
}
