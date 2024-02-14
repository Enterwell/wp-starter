/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Toolbar, ToolbarButton, Slot, Fill } from '@wordpress/components';
import { useViewportMatch } from '@wordpress/compose';
import { category as dashboardIcon } from '@wordpress/icons';

/**
 * iThemes dependencies
 */
import { Surface } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { useGlobalNavigationUrl } from '@ithemes/security-utils';
import { Logo } from '@ithemes/security-ui';
import { PurpleShield } from '@ithemes/security-style-guide';

const StyledTopToolbar = styled( Surface )`
	display: flex;
	align-items: center;
	flex-shrink: 0;
	padding: .5rem 1.5rem;
	min-height: calc(30px + 1rem);

	& .components-button:focus {
		// Prevent a double focus border.
		box-shadow: none;
	}
`;

const StyledToolbar = styled( Toolbar )`
	border: none;
	max-width: 100%;
	margin-left: auto;
`;

const StyledMainFill = styled.div`
	display: flex;
	gap: 0.5rem;
	margin: 0 2rem 0 1rem;
	flex-grow: 1;
`;

const StyledShield = styled( PurpleShield )`
	height: 2rem;
	width: 2rem;
`;

function ToolbarSlot( { area, ...props } ) {
	return <Slot name={ `Toolbar${ area }` } { ...props } />;
}

export function ToolbarFill( { area = 'actions', ...props } ) {
	return <Fill name={ `Toolbar${ area }` } { ...props } />;
}

export default function TopToolbar() {
	const dashboardUrl = useGlobalNavigationUrl( 'dashboard' );
	const isSmall = useViewportMatch( 'medium', '<' );

	return (
		<StyledTopToolbar role="region" aria-label={ __( 'Toolbar', 'better-wp-security' ) }>
			{ isSmall ? <StyledShield /> : <Logo /> }
			<ToolbarSlot area="main">
				{ ( fills ) => <StyledMainFill>{ fills }</StyledMainFill> }
			</ToolbarSlot>
			<StyledToolbar label={ __( 'Toolbar Actions', 'better-wp-security' ) }>
				<ToolbarButton
					icon={ dashboardIcon }
					href={ dashboardUrl }
					text={ __( 'Dashboard', 'better-wp-security' ) }
				/>
				<ToolbarSlot area="actions" />
			</StyledToolbar>
		</StyledTopToolbar>
	);
}
