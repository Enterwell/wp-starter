/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { ClipboardButton } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

/**
 * SolidWP dependencies
 */
import { Text } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { ToolFill } from '@ithemes/security.pages.tools';
import { useAsync } from '@ithemes/security-hocs';

function fetchConfig() {
	return apiFetch( {
		path: '/ithemes-security/rpc/file-writing/get-config-rules',
	} );
}

const StyledPre = styled.pre`
	background: #f1f1f1;
	overflow: scroll;
	padding: 1em;
	max-height: 300px;
`;

function Rules( { rules } ) {
	if ( ! rules.length ) {
		return (
			<Text
				text={ __( 'There are no rules that need to be written.', 'better-wp-security' ) }
			/>
		);
	}

	return <StyledPre>{ rules }</StyledPre>;
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
			{ value.server && (
				<ToolFill tool="server-config-rules">
					<Rules rules={ value.server } />
				</ToolFill>
			) }
			{ value.wp && (
				<ToolFill tool="wp-config-rules">
					<Rules rules={ value.wp } />
				</ToolFill>
			) }
			{ value.server && (
				<ToolFill tool="server-config-rules" area="actions">
					<Copy rules={ value.server } />
				</ToolFill>
			) }
			{ value.wp && (
				<ToolFill tool="wp-config-rules" area="actions">
					<Copy rules={ value.wp } />
				</ToolFill>
			) }
		</>
	);
}
