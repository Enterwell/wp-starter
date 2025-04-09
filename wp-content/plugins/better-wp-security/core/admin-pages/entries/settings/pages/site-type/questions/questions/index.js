/**
 * WordPress dependencies
 */
import { useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { useSingletonEffect } from '@ithemes/security-hocs';
import { STORE_NAME } from '../../../../stores/onboard';
import IsClient from './is-client';
import IpDetection from './ip-detection';
import ScanSite from './scan-site';
import Firewall from './firewall';
import TwoFactor from './two-factor';
import PasswordRequirements from './password-requirements';

export default function useQuestions() {
	const { registerQuestionComponent } = useDispatch( STORE_NAME );

	useSingletonEffect( useQuestions, () => {
		registerQuestionComponent( 'scan-site', ScanSite );
		registerQuestionComponent( 'firewall', Firewall );
		registerQuestionComponent( 'two-factor', TwoFactor );
		registerQuestionComponent( 'password-requirements', PasswordRequirements );
		registerQuestionComponent( 'is-client', IsClient );
		registerQuestionComponent( 'ip-detection', IpDetection );
	} );
}
