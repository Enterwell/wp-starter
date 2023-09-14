/**
 * WordPress dependencies
 */
import { ClipboardButton } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

/**
 * Internal dependencies
 */
import { ToolFill } from '@ithemes/security.pages.settings';
import { useAsync } from '@ithemes/security-hocs';
import './style.scss';

function fetchConfig() {
	return apiFetch( {
		path: '/ithemes-security/rpc/file-writing/get-config-rules',
	} );
}

function Rules( { rules } ) {
	if ( ! rules.length ) {
		return (
			<p>
				{ __( 'There are no rules that need to be written.', 'better-wp-security' ) }
			</p>
		);
	}

	return <pre className="itsec-file-writing-config-rules">{ rules }</pre>;
}

function Copy( { rules } ) {
	const [ copied, setCopied ] = useState( false );

	if ( ! rules.length ) {
		return null;
	}

	return (
		<ClipboardButton
			variant="secondary"
			text={ rules }
			onCopy={ () => setCopied( true ) }
			onFinishCopy={ () => setCopied( false ) }
		>
			{ copied ? __( 'Copied!', 'better-wp-security' ) : __( 'Copy Rules', 'better-wp-security' ) }
		</ClipboardButton>
	);
}

export default function App() {
	const { status, value } = useAsync( fetchConfig );

	if ( 'success' !== status ) {
		return null;
	}

	return (
		<>
			<ToolFill tool="server-config-rules">
				<Rules rules={ value.server } />
			</ToolFill>
			<ToolFill tool="wp-config-rules">
				<Rules rules={ value.wp } />
			</ToolFill>
			<ToolFill tool="server-config-rules" area="actions">
				<Copy rules={ value.server } />
			</ToolFill>
			<ToolFill tool="wp-config-rules" area="actions">
				<Copy rules={ value.wp } />
			</ToolFill>
		</>
	);
}
