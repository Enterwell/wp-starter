/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { useSelect } from '@wordpress/data';
import { useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { RjsfFieldFill } from '@ithemes/security-rjsf-theme';
import { useAsync } from '@ithemes/security-hocs';
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import './style.scss';

function useDetectedIp() {
	const { proxy, proxyHeader, schema } = useSelect( ( select ) => ( {
		proxy: select( MODULES_STORE_NAME ).getEditedSetting(
			'global',
			'proxy'
		),
		proxyHeader: select( MODULES_STORE_NAME ).getEditedSetting(
			'global',
			'proxy_header'
		),
		schema: select( MODULES_STORE_NAME ).getSettingSchema(
			'global',
			'proxy'
		),
	} ) );

	const execute = useCallback( () => {
		const data = {
			proxy: schema.enum.includes( proxy ) ? proxy : schema.default,
		};

		if ( data.proxy === 'manual' ) {
			data.args = { header: proxyHeader };
		}

		return apiFetch( {
			method: 'POST',
			path: 'ithemes-security/rpc/global/detect-ip',
			data,
		} );
	}, [ proxy, proxyHeader, schema ] );
	const { execute: detectIp, status, value, error } = useAsync(
		execute,
		!! proxy && !! schema
	);

	let label;

	switch ( status ) {
		case 'idle':
			break;
		case 'pending':
			label = __( 'Detecting IP', 'better-wp-security' );
			break;
		case 'success':
			/* translators: 1. IP address. */
			label = sprintf( __( 'Detected IP: %s', 'better-wp-security' ), value.ip );
			break;
		case 'error':
			label = sprintf(
				/* translators: 1. Error message. */
				__( 'Error detecting IP: %s', 'better-wp-security' ),
				error.message || __( 'Unknown error.' )
			);
			break;
	}

	return {
		label,
		detectIp,
		ip: value?.ip,
	};
}

function ProxyIP( { label, detectIp } ) {
	return (
		<div className="itsec-global-detected-ip">
			<Button variant="secondary" onClick={ detectIp }>
				{ __( 'Check IP', 'better-wp-security' ) }
			</Button>
			<span>{ label }</span>
		</div>
	);
}

function AuthorizedHosts( { value, onChange, ip } ) {
	const onClick = () => {
		onChange( [ ...value, ip ] );
	};

	return (
		<Button
			variant="secondary"
			onClick={ onClick }
			disabled={ ! ip }
			className="itsec-global-add-authorized-ip"
		>
			{ __( 'Add my current IP to the authorized hosts list', 'better-wp-security' ) }
		</Button>
	);
}

export default function App() {
	const { label, detectIp, ip } = useDetectedIp();

	return (
		<>
			<RjsfFieldFill name="itsec_global_lockout_white_list">
				{ ( { formData, onChange } ) => (
					<AuthorizedHosts
						value={ formData }
						onChange={ onChange }
						ip={ ip }
					/>
				) }
			</RjsfFieldFill>
			<RjsfFieldFill name="itsec_global_proxy">
				{ () => <ProxyIP label={ label } detectIp={ detectIp } /> }
			</RjsfFieldFill>
		</>
	);
}
