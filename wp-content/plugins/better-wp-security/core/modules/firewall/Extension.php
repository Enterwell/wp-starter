<?php

namespace iThemesSecurity\Modules\Firewall;

use iThemesSecurity\Lib\Lockout;
use iThemesSecurity\Strauss\Patchstack\Extensions\ExtensionInterface;

class Extension implements ExtensionInterface {

	/** @var \ITSEC_Lockout */
	private $lockout;

	public function __construct( \ITSEC_Lockout $lockout ) { $this->lockout = $lockout; }

	public function logRequest( $ruleId, $bodyData, $blockType ) {
		$code = $blockType . '::' . $ruleId;
		$data = [
			'body_data'  => $bodyData,
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
			'method'     => $_SERVER['REQUEST_METHOD'] ?? '',
		];

		if ( $blockType === 'BLOCK' ) {
			$context = new Lockout\Host_Context( 'firewall' );

			if ( \ITSEC_Lib_IP_Detector::is_configured() ) {
				$this->lockout->do_lockout( $context );
			} else {
				$this->lockout->execute_lock( $context->make_execute_lock_context() );
			}

			\ITSEC_Log::add_action( 'firewall', $code, $data );
		} else {
			\ITSEC_Log::add_notice( 'firewall', $code, $data );
		}
	}

	public function canBypass( $isMuCall ) {
		if ( ! \ITSEC_Lib_IP_Detector::is_configured() ) {
			return false;
		}

		return \ITSEC_Lib::is_ip_whitelisted( $this->getIpAddress() );
	}

	public function isBlocked( $minutes, $blockTime, $attempts ) {
		if ( ! \ITSEC_Lib_IP_Detector::is_configured() ) {
			return false;
		}

		return $this->lockout->is_host_locked_out( $this->getIpAddress() );
	}

	public function forceExit( $ruleId ) {
		$this->lockout->execute_lock( $this->lockout_context()->make_execute_lock_context() );
	}

	public function getIpAddress() {
		return \ITSEC_Lib::get_ip();
	}

	public function getHostName() {
		return parse_url( home_url(), PHP_URL_HOST );
	}

	public function isWhitelisted( $whitelistRules, $request ) {
		return false;
	}

	public function isFileUploadRequest() {
		return isset( $_FILES ) && count( $_FILES ) > 0;
	}

	private function lockout_context(): Lockout\Context {
		return new Lockout\Host_Context( 'firewall' );
	}
}
