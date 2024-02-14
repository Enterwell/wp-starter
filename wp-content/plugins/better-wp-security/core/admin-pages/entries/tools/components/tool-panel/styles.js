/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Card, CardBody } from '@wordpress/components';

/**
 * SolidWP dependencies
 */
import { Heading, MessageList } from '@ithemes/ui';
import { Markup } from '@ithemes/security-ui';

export const StyledCardContainer = styled.div`
	display: grid;
	grid-template-columns: repeat( auto-fit, minmax(min(800px, 100%), 1fr) );
	gap: 2rem;
	margin-top: 1rem;
`;

export const StyledToolContainer = styled( Card )`
	display: flex;
	flex-direction: column;
	margin-bottom: 0.75rem;
	box-shadow: none !important;
	border: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
	> div:first-child {
		display: flex;
		flex-direction: column;
	}
`;

export const StyledToolHeading = styled( Heading )`
	font-size: 0.875rem;
`;

export const StyledCardBody = styled( CardBody )`
	flex-grow: 2;
	display: flex;
	flex-direction: column;
`;

export const StyledResult = styled.div`
	margin-bottom: 0.5rem;
`;

export const StyledTextContainer = styled.div`
	width: 90%;
`;

export const StyledHelpText = styled( Markup )`
	font-size: 0.75rem;
	color: ${ ( { theme } ) => theme.colors.text.muted };
`;

export const StyledInputContainer = styled.div`
	flex: 0 0 90%;
`;

export const StyledToolActionContainer = styled.div`
	display: flex;
	margin-top: auto;
	@media screen and (min-width: ${ ( { theme } ) => theme.breaks.medium }px) {
		flex-direction: ${ ( { hasMessage } ) => hasMessage ? 'column' : 'row' };
	}
`;

export const StyledToolActionMessage = styled( MessageList )`
	margin: 0.5rem 0;
`;
