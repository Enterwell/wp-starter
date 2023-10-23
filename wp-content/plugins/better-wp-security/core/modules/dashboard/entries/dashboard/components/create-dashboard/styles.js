/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Modal, TextControl } from '@wordpress/components';
import { useMediaQuery } from '@wordpress/compose';

/**
 * SolidWP dependencies
 */
import { Heading, Surface, Text } from '@ithemes/ui';

/**
 * Internal dependencies
 */
import { PurpleShield, ShieldOutline } from '@ithemes/security-style-guide';
import DefaultLayout from './default-layout.svg';
import ScratchLayout from './scratch-layout.svg';

export function DashboardIcon( { type } ) {
	const isHiRes = useMediaQuery( '(-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi)' );

	if ( type === 'default' ) {
		return (
			isHiRes ? <DefaultLayout /> : <StyledPurpleShield />
		);
	}
	return (
		isHiRes ? <ScratchLayout /> : <StyledShieldOutline />
	);
}

const StyledPurpleShield = styled( PurpleShield )`
	height: 80px;
	width: 80px;
`;

const StyledShieldOutline = styled( ShieldOutline )`
	margin: 0.5rem 0;
`;

export const StyledModal = styled( Modal )`
	width: 90%;
	max-width: 760px;
	& .components-modal__header .components-modal__header-heading {
		font-size: ${ ( { theme } ) => theme.getSize( theme.sizes.text.large ) };
	}
	& .components-modal__content {
		padding: 0 0 3.25rem;
	}
`;

export const StyledDashboardHeading = styled( Heading )`
	margin: 2rem 0 1rem 0;
`;

export const StyledContainer = styled( Surface )`
	display: grid;
	grid-template: auto / 1fr 1fr;
	margin-top: 2rem;
`;

export const StyledDashboard = styled.section`
	display: flex;
	flex-direction: column;
	align-items: stretch;
`;

export const StyledDefaultDashboard = styled( StyledDashboard )`
	border-right: 1px solid ${ ( { theme } ) => theme.colors.border.normal }
`;

export const StyledHeader = styled.header`
	display: flex;
	flex-direction: column;
	align-items: center;
	margin-bottom: 1.5rem;
`;

export const StyledForm = styled.form`
	display: flex;
	flex-direction: column;
	align-items: center;
`;

export const StyledTextControl = styled( TextControl )`
	width: 290px;
`;

export const StyledHelpText = styled( Text )`
	padding: 0.25rem 0 0.75rem;
	visibility: ${ ( { hasError } ) => hasError ? 'visible' : 'hidden' }
`;
