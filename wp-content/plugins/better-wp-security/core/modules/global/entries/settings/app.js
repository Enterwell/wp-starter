/**
 * WordPress dependencies
 */
import { Button, Flex } from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { useSelect } from '@wordpress/data';
import { createInterpolateElement, useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { RjsfFieldFill } from '@ithemes/security-rjsf-theme';
import { useAsync } from '@ithemes/security-hocs';
import { MODULES_STORE_NAME } from '@ithemes/security.packages.data';
import { OnboardSiteTypeIpDetectionFill } from '@ithemes/security.pages.settings';
import './style.scss';
import { Text } from '@ithemes/ui';

function useDetectedIp( proxyProvided, proxyHeaderProvided ) {
	const { proxySetting, proxyHeaderSetting, schema } = useSelect( ( select ) => ( {
		proxySetting: select( MODULES_STORE_NAME ).getEditedSetting(
			'global',
			'proxy'
		),
		proxyHeaderSetting: select( MODULES_STORE_NAME ).getEditedSetting(
			'global',
			'proxy_header'
		),
		schema: select( MODULES_STORE_NAME ).getSettingSchema(
			'global',
			'proxy'
		),
	} ), [] );

	const proxy = proxyProvided || proxySetting;
	const proxyHeader = proxyHeaderProvided || proxyHeaderSetting;

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
			{ __( 'Authorize my IP address', 'better-wp-security' ) }
		</Button>
	);
}

function Onboard( { proxy, proxyHeader } ) {
	const { label } = useDetectedIp( proxy, proxyHeader );

	return (
		<Flex direction="column" align="start">
			<Text
				as="p"
				text={ createInterpolateElement(
					__( 'Select the configuration that causes the “Detected IP” shown below to match your current IP address. <a>Don’t know your IP?</a>', 'better-wp-security' ),
					{
						// eslint-disable-next-line jsx-a11y/anchor-has-content
						a: <a
							href="https://go.solidwp.com/ip-checker"
							target="_blank" rel="noreferrer"
						/>,
					}
				) }
			/>

			<div className="itsec-global-detected-ip">
				<span>{ label }</span>
			</div>
		</Flex>
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
			<OnboardSiteTypeIpDetectionFill>
				{ ( { proxy, proxyHeader } ) =>
					<Onboard proxy={ proxy } proxyHeader={ proxyHeader } />
				}
			</OnboardSiteTypeIpDetectionFill>
		</>
	);
}
