<?php

namespace iThemesSecurity\Modules\Firewall;

use iThemesSecurity\Contracts\Runnable;
use iThemesSecurity\Lib\Result;
use iThemesSecurity\Modules\Firewall\Rules\Repository;
use iThemesSecurity\Modules\Firewall\Rules\Rule;
use iThemesSecurity\Modules\Firewall\Rules\Rules_Options;
use iThemesSecurity\Site_Scanner\Vulnerability;
use iThemesSecurity\Site_Scanner\Vulnerability_Issue;

class Ingestor implements Runnable {

	/** @var Repository */
	private $repository;

	public function __construct( Repository $repository ) { $this->repository = $repository; }

	public function run() {
		add_action( 'itsec_vulnerability_was_seen', [ $this, 'vulnerability_was_seen' ], 10, 2 );
		add_action( 'itsec_vulnerability_not_seen', [ $this, 'vulnerability_not_seen' ] );
	}

	/**
	 * Whenever a vulnerability has been seen, add in
	 * any new firewall rules.
	 *
	 * @param Vulnerability       $vulnerability
	 * @param Vulnerability_Issue $issue
	 *
	 * @return void
	 */
	public function vulnerability_was_seen( Vulnerability $vulnerability, Vulnerability_Issue $issue ) {
		if ( ! $rules = $issue->get_firewall_rules() ) {
			// If patchstack is no longer providing rules for this vulnerability.
			// clear them out.
			$deleted = $this->repository->delete_rules(
				( new Rules_Options() )
					->set_vulnerabilities( [ $vulnerability->get_id() ] )
					->set_providers( [ 'patchstack' ] )
			);

			if ( $vulnerability->is_patched() ) {
				if ( $vulnerability->is_software_active() ) {
					$vulnerability->unresolve();
				} else {
					$vulnerability->deactivated();
				}
			}

			$this->log_cleanup( $vulnerability, $deleted );

			return;
		}

		$title = $vulnerability->get_details()['title'] ?? sprintf(
			__( 'Fix vulnerability in %s', 'better-wp-security' ),
			$vulnerability->get_software_label()
		);

		$has_rule = false;

		foreach ( $rules as $rule ) {
			if (
				$rule['product_keys'] &&
				$vulnerability->get_software_type() === Vulnerability::T_PLUGIN &&
				! in_array( $vulnerability->get_plugin_file(), $rule['product_keys'], true )
			) {
				continue;
			}

			$rule_config = [
				'rules' => $rule['vpatch'],
				'type'  => 'BLOCK',
			];

			$existing = $this->repository->get_rules(
				( new Rules_Options() )
					->for_provider_ref( $rule['provider'], $rule['id'] )
			);

			if ( $existing->is_success() && count( $existing->get_data() ) === 1 ) {
				$rule_model = $existing->get_data()[0];

				if ( $rule_model->get_config() === $rule_config ) {
					$has_rule = true;
					continue;
				}

				$rule_model->set_config( $rule_config );
				$persisted = $this->repository->persist( $rule_model );
			} else {
				$persisted = $this->repository->persist( Rule::create(
					$rule['provider'],
					$rule['id'],
					$title,
					$vulnerability->get_id(),
					$rule_config
				) );
			}

			if ( $persisted->is_success() ) {
				$has_rule = true;
				\ITSEC_Log::add_action(
					'firewall',
					'auto-created-rule::' . $vulnerability->get_id()
				);
			} else {
				\ITSEC_Log::add_error(
					'firewall',
					'ingest-failed::' . $vulnerability->get_id(),
					[
						'error' => $persisted->get_error(),
					]
				);
			}
		}

		if ( $has_rule ) {
			$vulnerability->patched();
		}
	}

	/**
	 * If a vulnerability has not been seen in the latest
	 * Site Scan, and the vulnerability no longer affects
	 * their site, clean out those rules.
	 *
	 * @param Vulnerability $vulnerability
	 *
	 * @return void
	 */
	public function vulnerability_not_seen( Vulnerability $vulnerability ) {
		// Only clear out rules for vulnerabilities that have been resolved due
		// to the plugin/theme being updated or deleted.
		if ( ! $vulnerability->is_deleted() && ! $vulnerability->is_updated() ) {
			return;
		}

		$deleted = $this->repository->delete_rules(
			( new Rules_Options() )
				->set_vulnerabilities( [ $vulnerability->get_id() ] )
		);

		$this->log_cleanup( $vulnerability, $deleted );
	}

	private function log_cleanup( Vulnerability $vulnerability, Result $result ) {
		if ( ! $result->is_success() ) {
			\ITSEC_Log::add_error(
				'firewall',
				'rule-cleanup-failed::' . $vulnerability->get_id(),
				[
					'error' => $result->get_error(),
				]
			);
		} elseif ( $result->get_data() > 0 ) {
			\ITSEC_Log::add_debug(
				'firewall',
				'rule-cleaned::' . $vulnerability->get_id()
			);
		}
	}
}
