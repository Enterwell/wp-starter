/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * SolidWP dependencies
 */
import { MasterDetailBackButton, Surface, Text } from '@ithemes/ui';

export const StyledBansLabels = styled( Text, {
	shouldForwardProp: ( prop ) => prop !== 'accentColor',
} )`
	padding-left: ${ ( { theme: { getSize } } ) => getSize( 1.25 ) };
	border-left: 3px solid ${ ( { theme, accentColor } ) => accentColor || theme.colors.text.accent };
  	display: block;
`;

export const StyledBanColumnLabel = styled.td`
	width: 30%;
`;

export const StyledBanColumnComment = styled( Text )`
	width: 70%;
`;

export const StyledBannedUsersBan = styled( Surface )`
	display: flex;
	flex-direction: column;
	gap: ${ ( { theme: { getSize } } ) => getSize( 0.5 ) };
	height: 100%;
	padding: ${ ( { theme: { getSize } } ) =>
		`${ getSize( 0.875 ) } ${ getSize( 1 ) }` };
`;

export const StyledBackButton = styled( MasterDetailBackButton )`
	align-self: start;
`;

export const StyledBannedUsersMain = styled.div`
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	flex-wrap: nowrap;
	gap: ${ ( { theme: { getSize } } ) => getSize( 1 ) };
`;

export const StyledDetails = styled.dl`
	display: grid;
	grid-template: min-content / min-content 1fr;
	grid-gap: ${ ( { theme: { getSize } } ) =>
		`${ getSize( 0.5 ) } ${ getSize( 1 ) }` };
	margin: 0;
`;

export const StyledDD = styled.dd`
	margin: 0;
	display: inline;
`;

export const StyledBansColumnLabel = styled.th`
	width: ${ ( { className } ) => className === 'itsec-banned-ips-data' ? '30%' : '60%' };
`;

export const StyledBansColumnComment = styled( Text )`
	width: ${ ( { className } ) => className === 'itsec-banned-ips-data' ? '70%' : '40%' };
`;
