/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * SolidWP dependencies
 */
import { Surface } from '@ithemes/ui';

export const StyledCard = styled( Surface )`
	display: flex;
	flex-direction: column;
	align-items: stretch;
	gap: 2.5rem;
	max-width: 95ch;
	width: 100%;
	border: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
	padding: 2.5rem 3rem;
	border-radius: 0.25rem;
`;

export const StyledCardGraphic = styled.figure`
	background-color: #f0e8f9;
	margin: 0;
	padding: 0;
	display: flex;
	justify-content: ${ ( { position } ) => position === 'right' ? 'flex-end' : 'center' };
	padding-right: ${ ( { position } ) => position === 'right' && '7.5rem' };
`;

export const StyledPoweredBy = styled.div`
	display: flex;
	gap: 0.25rem;
	flex-direction: column;
	align-items: center;
	align-self: center;
	margin-top: 2rem;
`;

export const StyledResultsPage = styled( Surface, {
	shouldForwardProp: ( prop ) => prop !== 'isWide',
} )`
	display: flex;
	flex-direction: column;
	gap: 2.5rem;
	max-width: ${ ( { isWide } ) => isWide ? '115ch' : '95ch' };
	width: 100%;
`;

export const StyledResultsCard = styled( Surface )`
	display: flex;
	flex-direction: column;
	gap: 1.25rem;
	width: 100%;
	border: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
	padding: 1.5rem;
	border-radius: 0.25rem;
`;

export const StyledEnableScheduling = styled( Surface )`
	display: flex;
	justify-content: space-between;
	align-items: center;
	border: 1px solid ${ ( { theme } ) => theme.colors.border.normal };
	padding: 1rem;
`;
