<?php

namespace iThemesSecurity\Site_Scanner;

final class Multi_Fixer implements Fixer {

	/** @var Fixer[] */
	private $fixers;

	public function __construct( Fixer ...$fixers ) {
		$this->fixers = $fixers;
	}

	public function is_fixable( Issue $issue ) {
		foreach ( $this->fixers as $fixer ) {
			if ( $fixer->is_fixable( $issue ) ) {
				return true;
			}
		}

		return false;
	}

	public function can_user_fix( \WP_User $user, Issue $issue ) {
		foreach ( $this->fixers as $fixer ) {
			if ( $fixer->is_fixable( $issue ) ) {
				return $fixer->can_user_fix( $user, $issue );
			}
		}

		return new \WP_Error( 'itsec_site_scanner_fixer_not_fixable', __( 'This issue cannot be automatically fixed.', 'better-wp-security' ) );
	}

	public function fix( Issue $issue ) {
		foreach ( $this->fixers as $fixer ) {
			if ( $fixer->is_fixable( $issue ) ) {
				return $fixer->fix( $issue );
			}
		}

		return new \WP_Error( 'itsec_site_scanner_fixer_not_fixable', __( 'This issue cannot be automatically fixed.', 'better-wp-security' ) );
	}

	public function get_fix_label( Issue $issue ) {
		foreach ( $this->fixers as $fixer ) {
			if ( $fixer->is_fixable( $issue ) ) {
				return $fixer->get_fix_label( $issue );
			}
		}

		return '';
	}
}
