/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { RjsfFieldFill } from '@ithemes/security-rjsf-theme';
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import './style.scss';

function ResetApiKey() {
	const { editSettings } = useDispatch( MODULES_STORE_NAME );
	const onReset = () => {
		editSettings( 'network-brute-force', {
			api_key: '',
			api_secret: '',
			email: null,
		} );
	};

	return (
		<Button variant="secondary" onClick={ onReset }>
			{ __( 'Reset API Key', 'better-wp-security' ) }
		</Button>
	);
}

export default function App() {
	return (
		<>
			<RjsfFieldFill name="itsec_network-brute-force_api_key">
				{ () => <ResetApiKey /> }
			</RjsfFieldFill>
		</>
	);
}
